<div x-show="{{ $show }}" class="fixed inset-0 z-50 flex items-center justify-center px-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-xl"></div>
    <div class="relative w-full max-w-sm rounded-xl bg-white/15 backdrop-blur-2xl p-4 border border-white/20 shadow-2xl">
        <h2 class="mb-4 text-lg font-semibold text-white">{{ $title }}</h2>
        <form wire:submit="{{ $action }}">
            @include('livewire.partials.connection-form')
            <div class="mt-4 flex space-x-3">
                <button type="submit" 
                    class="flex-1 rounded-lg bg-blue-500/80 backdrop-blur-lg py-2 text-sm font-medium text-white hover:bg-blue-600/80 focus:outline-none transition-colors duration-200">
                    {{ $buttonText }}
                </button>
                <button type="button" wire:click="$set('{{ str_replace('$wire.', '', $show) }}', false)"
                    class="rounded-lg bg-white/10 backdrop-blur-lg px-4 py-2 text-sm font-medium text-white hover:bg-white/20 focus:outline-none transition-colors duration-200">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div> 