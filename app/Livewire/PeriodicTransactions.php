<?php
namespace App\Livewire;

use App\Models\PeriodicTransaction;
use App\Models\Tag;
use Livewire\Component;

class PeriodicTransactions extends Component
{

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
}
