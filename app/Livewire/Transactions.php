<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Inventory;
use App\Models\PeriodicTransaction;
use App\Models\Tag;
use App\Models\Transaction;
use Livewire\Component;
use Livewire\WithPagination;

class Transactions extends Component
{
    use WithPagination;

    public $categories, $tags;
    public $title, $amount, $type, $category_id, $transaction_date, $tag_ids = [];
    public $transactionId, $isEditing, $is_periodic = false, $showModal = false, $showFilter = false, $editInventory = false;
    public $search = '', $filterType = '', $filterCategory = '', $start_date = '', $end_date = '', $tagSearch = '';
    public $perPage = 20, $inventory = 0;
    public $currentUser;

    protected $rules = [
        'title' => 'nullable|string|max:255',
        'amount' => 'required|numeric|min:0',
        'type' => 'required|in:income,expense',
        'category_id' => 'nullable|exists:categories,id',
        'transaction_date' => 'required|date',
        'tag_ids' => 'nullable|array',
        'tag_ids.*' => 'exists:tags,id',
        'tagSearch' => 'nullable|string|max:255',
    ];

    public function mount()
    {
        $this->currentUser = auth()->user();
        $this->categories = Category::all();
        $this->tags = Tag::all();
        // Set start_date to the 26th of the current month
        $this->start_date = now()->day >= 26
            ? now()->startOfMonth()->addDays(25)->format('Y-m-d')
            : now()->startOfMonth()->subMonth()->addDays(25)->format('Y-m-d');
        // Set end_date to the 25th of the next month
        $this->end_date = now()->day >= 26
            ? now()->startOfMonth()->addMonth()->addDays(24)->format('Y-m-d')
            : now()->startOfMonth()->addDays(24)->format('Y-m-d');
    }

    public function updated($name, $value)
    {
        if ($name == 'tagSearch') {
            $this->tags = Tag::when($this->tagSearch, function ($query) {
                $query->where('name', 'like', '%' . $this->tagSearch . '%');
            })->get();
        }
    }

    public function filterCollapse()
    {
        $this->showFilter = !$this->showFilter;
    }

    public function clearFilter()
    {
        $this->reset();
        $this->mount();
    }


    public function editInventoryCollapse()
    {
        $this->editInventory = !$this->editInventory;
    }

    public function saveInventory()
    {
        $this->validate([
            'inventory' => 'required|numeric|min:0',
        ]);

        $inventory = Inventory::firstOrCreate(
            ['user_id' => $this->currentUser->id],
            ['quantity' => 0]
        );
        $inventory->quantity = $this->inventory;
        $inventory->save();
        $this->editInventory = false;
    }

    public function render()
    {
        $this->inventory = $this->currentUser->inventory->quantity ?? 0;
        $userId = $this->currentUser->id;

        // Base query with eager loading
        $query = Transaction::query()
            ->where('user_id', $userId)
            ->with(['category', 'tags'])
            ->latest();

        // Apply dynamic filters
        $query->when($this->search, function ($q) {
            $q->where(function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%')
                    ->orWhereHas('category', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('tags', fn($q) => $q->where('name', 'like', '%' . $this->search . '%'));
            });
        })->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterCategory, fn($q) => $q->where('category_id', $this->filterCategory))
            ->when($this->start_date && $this->end_date, fn($q) => $q->whereBetween('transaction_date', [$this->start_date, $this->end_date]))
            ->when($this->start_date && !$this->end_date, fn($q) => $q->where('transaction_date', '>=', $this->start_date))
            ->when(!$this->start_date && $this->end_date, fn($q) => $q->where('transaction_date', '<=', $this->end_date));

        // Calculate totals in a single query
        $totals = $query->clone()
            ->selectRaw('
                SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as income_total,
                SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as expense_total
            ')
            ->first();

        $income_total = $totals->income_total ?? 0;
        $expense_total = $totals->expense_total ?? 0;

        // Paginate transactions
        $transactions = $query->paginate($this->perPage);

        return view('livewire.transactions', compact('transactions', 'income_total', 'expense_total'))
            ->layout('layouts.app');
    }

    public function create($type)
    {
        $this->resetInput();
        $this->type = $type;
        $this->transaction_date = date('Y-m-d');
        $this->showModal = true;
        $this->isEditing = false;
    }

    public function edit($id)
    {
        $transaction = Transaction::with('tags')->findOrFail($id);
        $this->transactionId = $id;
        $this->title = $transaction->title;
        $this->amount = $transaction->amount;
        $this->type = $transaction->type;
        $this->category_id = $transaction->category_id;
        $this->transaction_date = $transaction->transaction_date->format('Y-m-d');
        $this->tag_ids = $transaction->tags->pluck('id')->toArray();
        $this->showModal = true;
        $this->isEditing = true;
    }

    public function save()
    {
        $this->validate();

        // Initialize or fetch inventory
        $inventory = Inventory::firstOrCreate(
            ['user_id' => $this->currentUser->id],
            ['quantity' => 0]
        );

        if ($this->isEditing) {
            // Handle inventory for updates
            $oldTransaction = Transaction::findOrFail($this->transactionId);
            $oldAmount = $oldTransaction->amount;
            $oldType = $oldTransaction->type;

            // Reverse old transaction effect
            $inventory->quantity -= ($oldType === 'income' ? $oldAmount : -$oldAmount);
            // Apply new transaction effect
            $inventory->quantity += ($this->type === 'income' ? $this->amount : -$this->amount);
        } else {
            // Handle inventory for new transactions
            $inventory->quantity += ($this->type === 'income' ? $this->amount : -$this->amount);
        }

        $transaction = Transaction::updateOrCreate(
            ['id' => $this->transactionId],
            [
                'user_id' => $this->currentUser->id,
                'title' => $this->title,
                'amount' => $this->amount,
                'type' => $this->type,
                'category_id' => $this->category_id ?: null,
                'transaction_date' => $this->transaction_date,
            ]
        );

        // Save inventory changes
        $inventory->save();
        $transaction->tags()->sync($this->tag_ids);

        // Register as periodic if checked
        if ($this->is_periodic && !$this->isEditing) {
            PeriodicTransaction::create([
                'user_id' => $this->currentUser->id,
                'title' => $this->title,
                'amount' => $this->amount,
                'type' => $this->type,
                'category_id' => $this->category_id ?: null,
                'transaction_date' => $this->transaction_date,
                'tag_ids' => $this->tag_ids,
                'is_active' => true,
            ]);
        }

        $this->showModal = false;
        $this->resetInput();
        $this->tags = Tag::all();
    }

    public function delete($id)
    {
        $transaction = Transaction::findOrFail($id);
        $inventory = Inventory::where('user_id', $transaction->user_id)->first();
        if ($inventory) {
            // Reverse transaction effect on deletion
            $inventory->quantity -= ($transaction->type === 'income' ? $transaction->amount : -$transaction->amount);
            $inventory->save();
        }
        $transaction->delete();
    }

    public function addNewTag()
    {
        $tag = Tag::create([
            'name' => $this->tagSearch
        ]);
        $this->updated('tagSearch', $this->tagSearch);
        $this->tag_ids[] = $tag->id;
    }

    private function resetInput()
    {
        $this->title = '';
        $this->amount = '';
        $this->type = '';
        $this->category_id = '';
        $this->transaction_date = '';
        $this->tag_ids = [];
        $this->transactionId = null;
        $this->tagSearch = '';
        $this->is_periodic = false;
    }
}
