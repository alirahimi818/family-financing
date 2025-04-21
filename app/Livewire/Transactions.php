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
    public $transactionId, $isEditing, $is_periodic = false, $showModal = false;
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
        $this->start_date = now()->startOfMonth()->format('Y-m-d');
        $this->end_date = now()->endOfMonth()->format('Y-m-d');
    }

    public function updated($name, $value)
    {
        if ($name == 'tagSearch') {
            $this->tags = Tag::when($this->tagSearch, function ($query) {
                $query->where('name', 'like', '%' . $this->tagSearch . '%');
            })->get();
        }
    }

    public function render()
    {
        $this->inventory = $this->currentUser->inventory->quantity ?? 0;
        $transactions = Transaction::query()
            ->when($this->search, function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                    ->orWhereHas('category', function ($query) {
                        $query->where('name', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('tags', function ($query) {
                        $query->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterCategory, fn($q) => $q->where('category_id', $this->filterCategory))
            ->when($this->start_date, fn($q) => $q->whereDate('transaction_date', '>=', $this->start_date))
            ->when($this->end_date, fn($q) => $q->whereDate('transaction_date', '<=', $this->end_date))
            ->with(['category', 'tags'])
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.transactions', compact('transactions'))->layout('layouts.app');
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

        // Update inventory
        $inventory = Inventory::firstOrCreate(
            ['user_id' => $transaction->user_id],
            ['quantity' => 0]
        );
        $inventory->quantity += ($this->type === 'income' ? $this->amount : -$this->amount);
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
    }

    public function delete($id)
    {
        $transaction = Transaction::findOrFail($id);
        $inventory = Inventory::where('user_id', $transaction->user_id)->first();
        if ($inventory) {
            $inventory->quantity -= ($transaction->type === 'income' ? $transaction->amount : -$transaction->amount);
            $inventory->save();
        }
        $transaction->delete();
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
    }
}
