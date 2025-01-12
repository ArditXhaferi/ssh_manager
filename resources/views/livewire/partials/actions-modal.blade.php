<div
    x-data="{ show: false, connection: null }"
    @open-actions.window="show = true; connection = $event.detail.connection"
    x-show="show"
    class="fixed inset-0 z-50 flex items-end sm:items-center justify-center px-4 pb-6 sm:pb-0"
    x-cloak
>
    <div 
        x-show="show"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm"
        @click="show = false"
        x-transition:enter="transition-opacity ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    ></div>

    <div
        x-show="show"
        class="relative w-full max-w-xs rounded-2xl bg-white/15 backdrop-blur-xl p-4 border border-white/20 shadow-2xl transform transition-all"
        x-transition:enter="transition-transform ease-out duration-300"
        x-transition:enter-start="translate-y-8"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transition-transform ease-in duration-200"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-8"
    >
        <div class="space-y-2">
            <button 
                class="w-full text-left px-3 py-2 rounded-lg text-sm text-white hover:bg-white/10 transition-colors duration-200"
                wire:click="editConnection(connection.id)"
                @click="show = false"
            >
                Edit Connection
            </button>
            <button 
                class="w-full text-left px-3 py-2 rounded-lg text-sm text-red-300 hover:bg-red-500/10 transition-colors duration-200"
                wire:click="deleteConnection(connection.id)"
                @click="show = false"
            >
                Delete Connection
            </button>
            <button 
                class="w-full text-left px-3 py-2 rounded-lg text-sm text-white hover:bg-white/10 transition-colors duration-200"
                @click="show = false"
            >
                Cancel
            </button>
        </div>
    </div>
</div> 