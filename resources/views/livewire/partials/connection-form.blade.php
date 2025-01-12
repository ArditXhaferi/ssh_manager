<div class="space-y-3">
    <div>
        <label class="block text-sm font-medium text-gray-200">Name</label>
        <input type="text" wire:model="connection.name" 
            class="mt-1 w-full rounded-lg bg-white/10 backdrop-blur-xl px-3 py-2 text-sm text-white placeholder-gray-400 border border-white/10 focus:border-blue-500/50 focus:outline-none focus:ring-1 focus:ring-blue-500/50">
    </div>
    <div class="grid grid-cols-2 gap-2">
        <div>
            <label class="block text-sm font-medium text-gray-200">Username</label>
            <input type="text" wire:model="connection.username" 
                class="mt-1 w-full rounded-lg bg-white/10 backdrop-blur-xl px-3 py-2 text-sm text-white placeholder-gray-400 border border-white/10 focus:border-blue-500/50 focus:outline-none focus:ring-1 focus:ring-blue-500/50">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-200">Port</label>
            <input type="number" wire:model="connection.port" 
                class="mt-1 w-full rounded-lg bg-white/10 backdrop-blur-xl px-3 py-2 text-sm text-white placeholder-gray-400 border border-white/10 focus:border-blue-500/50 focus:outline-none focus:ring-1 focus:ring-blue-500/50">
        </div>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-200">Host</label>
        <input type="text" wire:model="connection.host" 
            class="mt-1 w-full rounded-lg bg-white/10 backdrop-blur-xl px-3 py-2 text-sm text-white placeholder-gray-400 border border-white/10 focus:border-blue-500/50 focus:outline-none focus:ring-1 focus:ring-blue-500/50">
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-200">Password</label>
        <input type="password" wire:model="connection.password" 
            class="mt-1 w-full rounded-lg bg-white/10 backdrop-blur-xl px-3 py-2 text-sm text-white placeholder-gray-400 border border-white/10 focus:border-blue-500/50 focus:outline-none focus:ring-1 focus:ring-blue-500/50">
    </div>
    <div class="mt-2">
        <label class="flex items-center">
            <input type="checkbox" wire:model="connection.locked" class="rounded border-gray-300 text-[#0A84FF] shadow-sm focus:border-[#0A84FF] focus:ring-[#0A84FF]">
            <span class="ml-2 text-xs text-gray-400">Require Passkey to Connect</span>
        </label>
    </div>
</div> 