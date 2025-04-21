<div class="max-w-6xl mx-auto">
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <div class="flex flex-wrap justify-between items-center mb-4">
            <h1 class="text-2xl font-semibold text-gray-800">Manage Transactions</h1>
            <div class="flex flex-wrap items-center gap-2">
                <div class="mr-1 font-bold">Inventory: {{number_format($inventory)}} €</div>
                <button wire:click="create('income')"
                        class="bg-green-600 text-white p-2 rounded-md hover:bg-green-700 flex items-center transition">
                    <span class="material-icons-outlined">add_circle_outline</span>
                </button>
                <button wire:click="create('expense')"
                        class="bg-red-600 text-white p-2 rounded-md hover:bg-red-700 flex items-center transition">
                    <span class="material-icons-outlined">remove_circle_outline</span>
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-6 flex flex-col sm:flex-row sm:space-x-4 space-y-4 sm:space-y-0">
            <input wire:model.live="search" type="text" placeholder="Search by title..."
                   class="w-full sm:w-1/3 border rounded-md p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <select wire:model.live="filterType"
                    class="w-full sm:w-1/3 border rounded-md p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">All Types</option>
                <option value="income">Income</option>
                <option value="expense">Expense</option>
            </select>
            <select wire:model.live="filterCategory"
                    class="w-full sm:w-1/3 border rounded-md p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <input wire:model.live="start_date" type="date" placeholder="Start Date" class="w-full sm:w-1/4 border rounded-md p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <input wire:model.live="end_date" type="date" placeholder="End Date" class="w-full sm:w-1/4 border rounded-md p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>

        <!-- Modal -->
        @if($showModal)
            <div
                class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 transition-opacity duration-300">
                <div class="bg-white rounded-lg p-6 w-full max-w-lg mx-4 sm:mx-auto transform transition-all border-4 {{ $type == 'income' ? 'border-green-500' : 'border-red-400' }}">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ $isEditing ? 'Edit Transaction' : 'Add Transaction' }}</h2>
                    <form wire:submit.prevent="save">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-medium mb-2">Amount</label>
                            <input wire:model="amount" type="number" step="0.01"
                                   class="w-full border rounded-md p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('amount') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-medium mb-2">Title (Optional)</label>
                            <input wire:model="title" type="text"
                                   class="w-full border rounded-md p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('title') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-medium mb-2">Category (Optional)</label>
                            <select wire:model="category_id"
                                    class="w-full border rounded-md p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <span
                                class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-medium mb-2">Tags (Optional)</label>
                            <input wire:model.live.debounce="tagSearch" type="text" placeholder="Search tags..." class="w-full border rounded-md p-2 mb-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <div class="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto">
                                @forelse($tags as $tag)
                                    <label class="flex items-center space-x-2 w-fit" wire:key="{{$tag->id}}">
                                        <input wire:model.live="tag_ids" type="checkbox" value="{{ $tag->id }}" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                        <span class="text-gray-700">{{ $tag->name }}</span>
                                    </label>
                                @empty
                                    <p class="text-gray-500 text-sm">No tags found.</p>
                                @endforelse
                            </div>
                            @error('tag_ids') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-medium mb-2">Date</label>
                            <input wire:model="transaction_date" type="date"
                                   class="w-full border rounded-md p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('transaction_date') <span
                                class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>
                        @if(!$isEditing)
                            <div class="mb-4">
                                <label class="flex items-center space-x-2">
                                    <input wire:model.live="is_periodic" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="text-gray-700">Register as monthly periodic transaction</span>
                                </label>
                            </div>
                        @endif
                        <div class="flex justify-end space-x-3">
                            <button type="button" wire:click="$set('showModal', false)"
                                    class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 flex items-center transition">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Cancel
                            </button>
                            <button type="submit"
                                    class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 flex items-center transition">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 13l4 4L19 7"></path>
                                </svg>
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Transactions Table -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse bg-white rounded-lg shadow-sm">
                <thead>
                <tr class="bg-gray-100 text-gray-700 text-sm uppercase tracking-wider sticky top-0">
                    <th class="p-3 text-left">Title</th>
                    <th class="p-3 text-left">Amount</th>
                    <th class="p-3 text-left">Type</th>
                    <th class="p-3 text-left">Category</th>
                    <th class="p-3 text-left">Tags</th>
                    <th class="p-3 text-left">Date</th>
                    <th class="p-3 text-left">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($transactions as $transaction)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="p-3 text-gray-800">{{ $transaction->title ?? '-' }}</td>
                        <td class="p-3 text-gray-800">{{ number_format($transaction->amount, 2) }} €</td>
                        <td class="p-3 text-gray-800">
                            <span
                                class="material-icons-outlined {{ $transaction->type == 'income' ? 'text-green-600' : 'text-red-600' }}">{{ $transaction->type == 'income' ? 'arrow_upward' : 'arrow_downward' }}</span>
                        </td>
                        <td class="p-3 text-gray-800">{{ $transaction->category->name ?? '-' }}</td>
                        <td class="p-3 text-gray-800">{{ $transaction->tags->pluck('name')->join(', ') ?: '-' }}</td>
                        <td class="p-3 text-gray-800">{{ $transaction->transaction_date->format('Y-m-d') }}</td>
                        <td class="p-3">
                            <div class="flex space-x-2">
                                <button wire:click="edit({{ $transaction->id }})"
                                        class="text-blue-600 hover:text-blue-800 flex items-center">
                                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15.414H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Edit
                                </button>
                                <button wire:click="delete({{ $transaction->id }})"
                                        class="text-red-600 hover:text-red-800 flex items-center">
                                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4a2 2 0 012 2v2H8V5a2 2 0 012-2z"></path>
                                    </svg>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-3 text-center text-gray-500">No transactions found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
