<div class="relative rounded-2xl backdrop-blur-2xl bg-black/10">
    <!-- Notification Toast -->
    <div 
        x-data="{ show: false }" 
        x-show="show" 
        @clipboard-copied.window="
            show = true;
            setTimeout(() => show = false, 2000);
        "
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="absolute bottom-6 left-1/2 transform -translate-x-1/2 px-4 py-2 rounded-lg bg-white/10 backdrop-blur-xl text-white text-sm font-medium shadow-lg"
        style="display: none;"
    >
        Copied to clipboard
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 mb-6">
        <button
            wire:click="generateNewKey"
            class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-900 transition-all duration-200"
        >
            <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Generate Key</span>
        </button>
        
        <button
            wire:click="openSshDirectory"
            class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white/80 hover:text-white bg-white/5 hover:bg-white/10 rounded-lg focus:outline-none focus:ring-2 focus:ring-white/20 transition-all duration-200"
        >
            <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
            </svg>
            <span>Open SSH Folder</span>
        </button>
    </div>

    <!-- Keys List -->
    @if(empty($publicKeys))
        <div class="flex flex-col items-center justify-center py-12 text-white/60">
            <svg class="w-12 h-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
            </svg>
            <p class="text-sm">No SSH keys found in your ~/.ssh directory</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($publicKeys as $index => $key)
                <div class="p-4 rounded-lg bg-white/5 hover:bg-white/[0.07] border border-white/10 transition-colors duration-200">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-medium text-white">{{ $key['name'] }}</span>
                        <div class="flex items-center space-x-2">
                            <button
                                wire:click="copyPublicKey({{ $index }})"
                                class="p-2 text-white/80 hover:text-white bg-white/5 hover:bg-white/10 rounded-md transition-colors duration-200"
                                title="Copy to clipboard"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                </svg>
                            </button>
                            <button
                                wire:click="deleteKey({{ $index }})"
                                wire:confirm="Are you sure you want to delete this SSH key? This action cannot be undone."
                                class="p-2 text-red-400 hover:text-red-300 bg-red-500/10 hover:bg-red-500/20 rounded-md transition-colors duration-200"
                                title="Delete key"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="text-xs font-mono text-white/60 break-all bg-black/20 rounded-lg p-3">
                        {{ $key['content'] }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('copyToClipboard', (event) => {
                navigator.clipboard.writeText(event.content).then(() => {
                    window.dispatchEvent(new CustomEvent('clipboard-copied'));
                });
            });
        });
    </script>
</div>
