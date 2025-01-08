<div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900">
    <!-- Main Content -->
    <main class="p-4">
        <div class="grid gap-3 md:grid-cols-3 lg:grid-cols-4">
            @foreach($connections as $connection)
                <div class="rounded-lg backdrop-blur-xl bg-white/10 p-4 border border-white/20 shadow-lg hover:bg-white/15 transition-all duration-300">
                    <div class="mb-2 flex items-center justify-between">
                        <h2 class="text-base font-medium text-white">{{ $connection['name'] }}</h2>
                        <div class="flex items-center">
                            @if($connection['is_healthy'] ?? false)
                                <span class="flex h-2 w-2 rounded-full bg-green-400 shadow-green-400/50 shadow-lg"></span>
                            @else
                                <span class="flex h-2 w-2 rounded-full bg-red-400 shadow-red-400/50 shadow-lg"></span>
                            @endif
                        </div>
                    </div>
                    <div class="space-y-1 text-sm text-gray-200">
                        <p><span class="font-medium">Host:</span> {{ $connection['host'] }}</p>
                        <p><span class="font-medium">Username:</span> {{ $connection['username'] }}</p>
                        <p><span class="font-medium">Port:</span> {{ $connection['port'] }}</p>
                    </div>
                    <div class="mt-3 flex space-x-2">
                        <button 
                            class="flex-1 rounded-md bg-blue-500/80 backdrop-blur-lg py-1.5 text-sm font-medium text-white hover:bg-blue-600/80 focus:outline-none transition-colors duration-200"
                            wire:click="startConnection({{ $connection['id'] }})"
                        >
                            Connect
                        </button>
                        <button 
                            class="rounded-md bg-white/10 backdrop-blur-lg px-3 py-1.5 text-sm font-medium text-white hover:bg-white/20 focus:outline-none transition-colors duration-200"
                            wire:click="editConnection({{ $connection['id'] }})"
                        >
                            Edit
                        </button>
                        <button 
                            class="rounded-md bg-red-500/20 backdrop-blur-lg px-3 py-1.5 text-sm font-medium text-red-300 hover:bg-red-500/30 focus:outline-none transition-colors duration-200"
                            wire:click="deleteConnection({{ $connection['id'] }})"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </main>

    <!-- Add Connection Button (Sticky) -->
    <div class="fixed bottom-6 right-6">
        <button 
            wire:click="$set('showNewModal', true)"
            class="rounded-full h-10 w-10 backdrop-blur-xl bg-white/10 border border-white/20 flex items-center justify-center hover:bg-white/20 focus:outline-none shadow-lg transition-all duration-300"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
        </button>
    </div>

    <!-- New Connection Modal -->
    <div x-show="$wire.showNewModal" class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-xl"></div>
        <div class="relative w-full max-w-sm rounded-xl bg-white/15 backdrop-blur-2xl p-4 border border-white/20 shadow-2xl">
            <h2 class="mb-4 text-lg font-semibold text-white">New SSH Connection</h2>
            <form wire:submit="addConnection">
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-200">Name</label>
                        <input type="text" wire:model="newConnection.name" 
                            class="mt-1 w-full rounded-lg bg-white/10 backdrop-blur-xl px-3 py-2 text-sm text-white placeholder-gray-400 border border-white/10 focus:border-blue-500/50 focus:outline-none focus:ring-1 focus:ring-blue-500/50">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-200">Username</label>
                            <input type="text" wire:model="newConnection.username" 
                                class="mt-1 w-full rounded-lg bg-white/10 backdrop-blur-xl px-3 py-2 text-sm text-white placeholder-gray-400 border border-white/10 focus:border-blue-500/50 focus:outline-none focus:ring-1 focus:ring-blue-500/50">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-200">Port</label>
                            <input type="number" wire:model="newConnection.port" 
                                class="mt-1 w-full rounded-lg bg-white/10 backdrop-blur-xl px-3 py-2 text-sm text-white placeholder-gray-400 border border-white/10 focus:border-blue-500/50 focus:outline-none focus:ring-1 focus:ring-blue-500/50">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-200">Host</label>
                        <input type="text" wire:model="newConnection.host" 
                            class="mt-1 w-full rounded-lg bg-white/10 backdrop-blur-xl px-3 py-2 text-sm text-white placeholder-gray-400 border border-white/10 focus:border-blue-500/50 focus:outline-none focus:ring-1 focus:ring-blue-500/50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-200">Password</label>
                        <input type="password" wire:model="newConnection.password" 
                            class="mt-1 w-full rounded-lg bg-white/10 backdrop-blur-xl px-3 py-2 text-sm text-white placeholder-gray-400 border border-white/10 focus:border-blue-500/50 focus:outline-none focus:ring-1 focus:ring-blue-500/50">
                    </div>
                    <div class="mt-2">
                        <label class="flex items-center">
                            <input type="checkbox" wire:model="newConnection.locked" class="rounded border-gray-300 text-[#0A84FF] shadow-sm focus:border-[#0A84FF] focus:ring-[#0A84FF]">
                            <span class="ml-2 text-xs text-gray-400">Require Passkey to Connect</span>
                        </label>
                    </div>
                </div>
                <div class="mt-4 flex space-x-3">
                    <button type="submit" 
                        class="flex-1 rounded-lg bg-blue-500/80 backdrop-blur-lg py-2 text-sm font-medium text-white hover:bg-blue-600/80 focus:outline-none transition-colors duration-200">
                        Add Connection
                    </button>
                    <button type="button" wire:click="$set('showNewModal', false)"
                        class="rounded-lg bg-white/10 backdrop-blur-lg px-4 py-2 text-sm font-medium text-white hover:bg-white/20 focus:outline-none transition-colors duration-200">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Connection Modal -->
    @if($selectedConnection)
    <div x-show="$wire.showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" x-on:click.self="$wire.showEditModal = false">
        <div class="w-full max-w-sm rounded-xl bg-gray-800 p-3">
            <h2 class="mb-2 text-base font-semibold text-gray-100">Edit SSH Connection</h2>
            <form wire:submit="updateConnection">
                <div class="space-y-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-200">Name</label>
                        <input type="text" wire:model="selectedConnection.name" 
                            class="mt-1 w-full rounded-lg bg-white/10 backdrop-blur-xl px-3 py-2 text-sm text-white placeholder-gray-400 border border-white/10 focus:border-blue-500/50 focus:outline-none focus:ring-1 focus:ring-blue-500/50">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-200">Username</label>
                            <input type="text" wire:model="selectedConnection.username" 
                                class="mt-1 w-full rounded-lg bg-white/10 backdrop-blur-xl px-3 py-2 text-sm text-white placeholder-gray-400 border border-white/10 focus:border-blue-500/50 focus:outline-none focus:ring-1 focus:ring-blue-500/50">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-200">Port</label>
                            <input type="number" wire:model="selectedConnection.port" 
                                class="mt-1 w-full rounded-lg bg-white/10 backdrop-blur-xl px-3 py-2 text-sm text-white placeholder-gray-400 border border-white/10 focus:border-blue-500/50 focus:outline-none focus:ring-1 focus:ring-blue-500/50">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-200">Host</label>
                        <input type="text" wire:model="selectedConnection.host" 
                            class="mt-1 w-full rounded-lg bg-white/10 backdrop-blur-xl px-3 py-2 text-sm text-white placeholder-gray-400 border border-white/10 focus:border-blue-500/50 focus:outline-none focus:ring-1 focus:ring-blue-500/50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-200">Password</label>
                        <input type="password" wire:model="selectedConnection.password" 
                            class="mt-1 w-full rounded-lg bg-white/10 backdrop-blur-xl px-3 py-2 text-sm text-white placeholder-gray-400 border border-white/10 focus:border-blue-500/50 focus:outline-none focus:ring-1 focus:ring-blue-500/50">
                    </div>
                </div>
                <div class="mt-3 flex space-x-2">
                    <button type="submit" class="flex-1 rounded-lg bg-[#0A84FF] py-1 text-xs font-medium text-white hover:bg-[#0A84FF]/90 focus:outline-none">
                        Save Changes
                    </button>
                    <button type="button" wire:click="$set('showEditModal', false)" class="rounded-lg bg-[#3C3C3E] px-3 py-1 text-xs font-medium text-white hover:bg-[#4C4C4E] focus:outline-none">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>