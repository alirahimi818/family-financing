<div class="max-w-6xl mx-auto">
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <div class="flex flex-wrap justify-between items-center mb-4">
            <h1 class="text-2xl font-semibold text-gray-800">Periodic Transactions</h1>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border-collapse bg-white rounded-lg shadow-sm">
                <thead>
                <tr class="bg-gray-100 text-gray-700 text-sm uppercase tracking-wider">
                    <th class="p-3 text-left">Title</th>
                    <th class="p-3 text-left">Amount</th>
                    <th class="p-3 text-left">Type</th>
                    <th class="p-3 text-left">Category</th>
                    <th class="p-3 text-left">Tags</th>
                    <th class="p-3 text-left">Date</th>
                    <th class="p-3 text-left">Status</th>
                    <th class="p-3 text-left">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($periodicTransactions as $periodic)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="p-3 text-gray-800">{{ $periodic->title ?? '-' }}</td>
                        <td class="p-3 text-gray-800">{{ number_format($periodic->amount, 2) }} €</td>
                        <td class="p-3 text-gray-800">{{ ucfirst($periodic->type) }}</td>
                        <td class="p-3 text-gray-800">{{ $periodic->category?->name ?? '-' }}</td>
                        <td class="p-3 text-gray-800">
                            @php
                                $tagNames = \App\Models\Tag::whereIn('id', $periodic->tag_ids ?? [])->pluck('name')->join(', ');
                            @endphp
                            {{ $tagNames ?: '-' }}
                        </td>
                        <td class="p-3 text-gray-800">{{ $periodic->transaction_date }}</td>
                        <td class="p-3 text-gray-800">{{ $periodic->is_active ? 'Active' : 'Inactive' }}</td>
                        <td class="p-3">
                            <div class="flex space-x-2">
                                <button wire:click="togglePeriodic({{ $periodic->id }})"
                                        class="text-blue-600 hover:text-blue-800 flex items-center">
                                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                         xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $periodic->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                                <button wire:click="delete({{ $periodic->id }})" wire:confirm="Are you sure?"
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
                        <td colspan="9" class="p-3 text-center text-gray-500">No periodic transactions found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
