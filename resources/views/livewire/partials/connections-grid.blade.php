<main class="p-4">
    <div class="grid gap-2 md:grid-cols-4 lg:grid-cols-5">
        @foreach($connections as $connection)
            <div class="group relative rounded-xl backdrop-blur-xl bg-white/10 p-3 border border-white/20 shadow-lg hover:bg-white/15 transition-all duration-300">
                <div class="flex items-center space-x-2">
                    <div class="flex-1 truncate">
                        <h2 class="text-sm font-medium text-white truncate">{{ $connection['name'] }}</h2>
                        <p class="text-xs text-gray-400 truncate">{{ $connection['username'] . "@" }}{{ $connection['host'] }}</p>
                    </div>
                    @if($connection['is_healthy'] ?? false)
                        <span class="flex h-1.5 w-1.5 rounded-full bg-green-400 shadow-green-400/50 shadow-sm"></span>
                    @else
                        <span class="flex h-1.5 w-1.5 rounded-full bg-red-400 shadow-red-400/50 shadow-sm"></span>
                    @endif
                </div>
                
                <div class="mt-2 flex items-center space-x-2">
                    <button 
                        class="flex-1 rounded-lg bg-blue-500/80 backdrop-blur-lg py-1.5 text-xs font-medium text-white hover:bg-blue-600/80 focus:outline-none transition-colors duration-200"
                        wire:click="startConnection({{ $connection['id'] }})"
                    >
                        Connect
                    </button>
                    
                    <button 
                        class="rounded-lg bg-white/10 backdrop-blur-lg p-1.5 text-white hover:bg-white/20 focus:outline-none transition-colors duration-200"
                        x-data
                        @click="$dispatch('open-actions', { connection: {{ Js::from($connection) }} })"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
                        </svg>
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</main> 