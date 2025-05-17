<?php
namespace App\Livewire;

use App\Models\Inventory;
use App\Models\PeriodicTransaction;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Carbon\Carbon;

class Dashboard extends Component
{
    public $month;
    public $year;
    public $availableYears;

    public function mount()
    {
        // Set default month and year based on the 26th
        $this->year = now()->day >= 26 ? now()->year : now()->subMonth()->year;
        $this->month = now()->day >= 26 ? now()->month : now()->subMonth()->month;
        // Dynamically fetch available years from transactions
        $this->availableYears = Transaction::where('user_id', auth()->id())
            ->selectRaw("DISTINCT strftime('%Y', transaction_date) as year")
            ->pluck('year')
            ->sort()
            ->values()
            ->toArray();
        // Ensure current year is included even if no transactions
        if (!in_array($this->year, $this->availableYears)) {
            $this->availableYears[] = $this->year;
            sort($this->availableYears);
        }
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['month', 'year'])) {
            $this->dispatch('refreshCharts');
        }
    }

    public function render()
    {
        $userId = auth()->id();

        // Calculate start and end dates for the selected period (26th to 25th)
        $startDate = Carbon::create($this->year, $this->month, 26)->startOfDay();
        $endDate = $startDate->copy()->addMonth()->subDay()->endOfDay();

        // Key Metrics
        $totalTransactions = Transaction::where('user_id', $userId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->count();
        $totalInventory = Inventory::where('user_id', $userId)->sum('quantity');
        $monthlyNetChange = Transaction::where('user_id', $userId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->selectRaw('SUM(CASE WHEN type = "income" THEN amount ELSE -amount END) as net')
            ->first()->net ?? 0;
        $periodicData = PeriodicTransaction::where('user_id', $userId)
            ->where('is_active', true)
            ->selectRaw('COUNT(*) as count,
                        SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as income_total,
                        SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as expense_total')
            ->first();
        $activePeriodicCount = $periodicData->count ?? 0;
        $activePeriodicIncomeTotal = $periodicData->income_total ?? 0;
        $activePeriodicExpenseTotal = $periodicData->expense_total ?? 0;

        // Line Chart Data (January to December for the selected year)
        $incomeData = [];
        $expenseData = [];
        for ($m = 1; $m <= 12; $m++) {
            // For each month m, the period is from the 26th of the previous month (m-1) to the 25th of month m
            $monthStart = Carbon::create($this->year, $m, 26)->subMonth()->startOfDay();
            $monthEnd = Carbon::create($this->year, $m, 25)->endOfDay();
            $income = Transaction::where('user_id', $userId)
                ->where('type', 'income')
                ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->sum('amount');
            $expense = Transaction::where('user_id', $userId)
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->sum('amount');
            $incomeData[] = (float) $income;
            $expenseData[] = (float) $expense;
        }

        // Pie Chart Data
        $categoryData = Transaction::where('user_id', $userId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->groupBy('categories.id', 'categories.name')
            ->selectRaw('categories.name, SUM(transactions.amount) as total')
            ->get()
            ->mapWithKeys(fn($item) => [$item->name => (float) $item->total])
            ->toArray();

        // Bar Chart Data
        $tagData = Transaction::where('user_id', $userId)
            ->where('transactions.type', 'expense')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->join('transaction_tag', 'transactions.id', '=', 'transaction_tag.transaction_id')
            ->join('tags', 'transaction_tag.tag_id', '=', 'tags.id')
            ->groupBy('tags.id', 'tags.name')
            ->selectRaw('tags.name, SUM(transactions.amount) as total')
            ->orderBy('total', 'desc')
            ->get()
            ->mapWithKeys(fn($item) => [$item->name => (float) $item->total])
            ->toArray();

        // Recent Transactions
        $recentTransactions = Transaction::where('user_id', $userId)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->with(['category', 'tags'])
            ->latest()
            ->take(5)
            ->get();

        // Log data for debugging
        Log::info('Dashboard data', [
            'month' => $this->month,
            'year' => $this->year,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'incomeData' => $incomeData,
            'expenseData' => $expenseData,
            'categoryData' => $categoryData,
            'tagData' => $tagData,
            'activePeriodicCount' => $activePeriodicCount,
            'activePeriodicIncomeTotal' => $activePeriodicIncomeTotal,
            'activePeriodicExpenseTotal' => $activePeriodicExpenseTotal,
        ]);

        return view('livewire.dashboard', compact(
            'totalTransactions',
            'totalInventory',
            'monthlyNetChange',
            'activePeriodicCount',
            'activePeriodicIncomeTotal',
            'activePeriodicExpenseTotal',
            'incomeData',
            'expenseData',
            'categoryData',
            'tagData',
            'recentTransactions'
        ))->layout('layouts.app');
    }
}
