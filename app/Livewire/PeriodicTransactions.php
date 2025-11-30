<?php
namespace App\Livewire;

use App\Models\PeriodicTransaction;
use Livewire\Component;

class PeriodicTransactions extends Component
{

    public $showModal = false;
    public $transactionId;
    public $title;
    public $amount;
    public $transaction_date;
    public $type;

    protected $rules = [
        'title' => 'nullable|string|max:255',
        'amount' => 'required|numeric|min:0',
        'transaction_date' => 'required|date',
    ];

    public function render()
    {
        $periodicTransactions = PeriodicTransaction::with(['user', 'category'])->get();
        return view('livewire.periodic-transactions', compact('periodicTransactions'))->layout('layouts.app');
    }

    public function togglePeriodic($id)
    {
        $periodic = PeriodicTransaction::findOrFail($id);
        $periodic->is_active = !$periodic->is_active;
        $periodic->save();
    }

    public function delete($id)
    {
        PeriodicTransaction::find($id)->delete();
    }

    public function edit($id)
    {
        $transaction = PeriodicTransaction::findOrFail($id);
        $this->transactionId = $id;
        $this->type = $transaction->type;
        $this->title = $transaction->title;
        $this->amount = $transaction->amount;
        $this->transaction_date = $transaction->transaction_date->format('Y-m-d');
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        PeriodicTransaction::updateOrCreate(
            ['id' => $this->transactionId],
            [
                'title' => $this->title,
                'amount' => $this->amount,
                'transaction_date' => $this->transaction_date,
            ]
        );

        $this->showModal = false;
        $this->resetInput();
    }

    private function resetInput()
    {
        $this->title = '';
        $this->amount = '';
        $this->type = '';
        $this->transaction_date = '';
        $this->transactionId = null;
    }
}
