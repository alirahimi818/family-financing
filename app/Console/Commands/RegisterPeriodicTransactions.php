<?php
namespace App\Console\Commands;

use App\Models\PeriodicTransaction;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RegisterPeriodicTransactions extends Command
{
    protected $signature = 'transactions:register-periodic';
    protected $description = 'Register active periodic transactions for the current month';

    public function handle()
    {
        $periodicTransactions = PeriodicTransaction::query()->where('is_active', true)->where('transaction_date', '<=' , now()->subMonth()->startOfDay())->get();
        foreach ($periodicTransactions as $periodic) {
            $newTransactionDate = Carbon::parse($periodic->transaction_date)->addMonth()->startOfDay();
            $transaction = Transaction::create([
                'user_id' => $periodic->user_id,
                'title' => $periodic->title,
                'amount' => $periodic->amount,
                'type' => $periodic->type,
                'category_id' => $periodic->category_id,
                'transaction_date' => $newTransactionDate,
            ]);

            if ($periodic->tag_ids) {
                $transaction->tags()->sync($periodic->tag_ids);
            }

            // Update inventory
            if ($transaction->user_id) {
                $inventory = \App\Models\Inventory::firstOrCreate(
                    ['user_id' => $transaction->user_id],
                    ['quantity' => 0]
                );
                $inventory->quantity += ($transaction->type === 'income' ? $transaction->amount : -$transaction->amount);
                $inventory->save();
            }
            $periodic->transaction_date = $newTransactionDate;
            $periodic->save();
        }

        $this->info('Periodic transactions registered successfully.');
    }
}
