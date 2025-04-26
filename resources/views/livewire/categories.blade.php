<div class="max-w-4xl mx-auto">
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-semibold text-gray-800">Manage Categories</h1>
            <button wire:click="create" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 flex items-center transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Category
            </button>
        </div>

        <!-- Modal -->
        @if($showModal)
            <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50 transition-opacity duration-300">
                <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4 sm:mx-auto transform transition-all">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">{{ $isEditing ? 'Edit Category' : 'Add Category' }}</h2>
                    <form wire:submit.prevent="save">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-medium mb-2">Name</label>
                            <input wire:model="name" type="text" class="w-full border rounded-md p-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            @error('name') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" wire:click="$set('showModal', false)" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 flex items-center transition">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                Cancel
                            </button>
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 flex items-center transition">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Save
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Categories Table -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse bg-white rounded-lg shadow-sm">
                <thead>
                <tr class="bg-gray-100 text-gray-700 text-sm uppercase tracking-wider">
                    <th class="p-3 text-left">Name</th>
                    <th class="p-3 text-left">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($categories as $category)
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="p-3 text-gray-800">{{ $category->name }}</td>
                        <td class="p-3">
                            <div class="flex space-x-2">
                                <button wire:click="edit({{ $category->id }})" class="text-blue-600 hover:text-blue-800 flex items-center">
                                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15.414H9v-2.828l8.586-8.586z"></path></svg>
                                    Edit
                                </button>
                                <button wire:click="delete({{ $category->id }})" wire:confirm="Are you sure?" class="text-red-600 hover:text-red-800 flex items-center">
                                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4a2 2 0 012 2v2H8V5a2 2 0 012-2z"></path></svg>
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="p-3 text-center text-gray-500">No categories found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
