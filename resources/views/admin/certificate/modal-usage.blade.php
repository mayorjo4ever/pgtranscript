{{-- Usage Examples --}}

{{-- Basic Modal --}}
<x-admin.modal name="basic-modal" title="Basic Modal">
    <p>This is a basic modal with just content.</p>
</x-admin.modal>

{{-- Modal with Custom Header --}}
<x-admin.modal name="custom-header-modal">
    <x-slot name="header">
        <div class="flex items-center space-x-2">
            <svg class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h3 class="text-lg font-semibold text-gray-900">Custom Header</h3>
        </div>
    </x-slot>
    
    <p>This modal has a custom header with an icon.</p>
</x-admin.modal>

{{-- Modal with Footer Actions --}}
<x-admin.modal name="action-modal" title="Confirm Action" size="lg">
    <p class="text-gray-600">Are you sure you want to delete this item? This action cannot be undone.</p>
    
    <x-slot name="footer">
        <button 
            @click="show = false"
            type="button" 
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
            Cancel
        </button>
        <button 
            type="button" 
            class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
        >
            Delete
        </button>
    </x-slot>
</x-admin.modal>

{{-- Form Modal --}}
<x-admin.modal name="form-modal" title="Create User" size="xl">
    <form class="space-y-4">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
            <input 
                type="text" 
                id="name" 
                name="name" 
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                placeholder="Enter full name"
            >
        </div>
        
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                placeholder="Enter email address"
            >
        </div>
        
        <div>
            <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
            <select 
                id="role" 
                name="role" 
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            >
                <option value="">Select a role</option>
                <option value="admin">Admin</option>
                <option value="user">User</option>
                <option value="moderator">Moderator</option>
            </select>
        </div>
    </form>
    
    <x-slot name="footer">
        <button 
            @click="show = false"
            type="button" 
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
            Cancel
        </button>
        <button 
            type="submit" 
            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
            Create User
        </button>
    </x-slot>
</x-admin.modal>

{{-- Modal with Header Actions --}}
<x-admin.modal name="header-actions-modal" title="Settings">
    <x-slot name="headerActions">
        <button class="text-gray-400 hover:text-gray-600">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
            </svg>
        </button>
    </x-slot>
    
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-700">Email Notifications</span>
            <button class="relative inline-flex h-6 w-11 items-center rounded-full bg-gray-200 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform translate-x-1"></span>
            </button>
        </div>
        
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-700">Push Notifications</span>
            <button class="relative inline-flex h-6 w-11 items-center rounded-full bg-blue-600 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform translate-x-6"></span>
            </button>
        </div>
    </div>
</x-admin.modal>

{{-- Buttons to trigger modals --}}
<div class="space-x-4">
    <button 
        @click="$dispatch('open-modal', 'basic-modal')"
        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
    >
        Open Basic Modal
    </button>
    
    <button 
        @click="$dispatch('open-modal', 'custom-header-modal')"
        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
    >
        Custom Header Modal
    </button>
    
    <button 
        @click="$dispatch('open-modal', 'action-modal')"
        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
    >
        Action Modal
    </button>
    
    <button 
        @click="$dispatch('open-modal', 'form-modal')"
        class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700"
    >
        Form Modal
    </button>
    
    <button 
        @click="$dispatch('open-modal', 'header-actions-modal')"
        class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"
    >
        Header Actions Modal
    </button>
</div>

{{-- JavaScript for programmatic control --}}
<script>
// Open modal programmatically
function openModal(name) {
    window.dispatchEvent(new CustomEvent('open-modal', { detail: name }));
}

// Close modal programmatically  
function closeModal(name) {
    window.dispatchEvent(new CustomEvent('close-modal', { detail: name }));
}
</script>