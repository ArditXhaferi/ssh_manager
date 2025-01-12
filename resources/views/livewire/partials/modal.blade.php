<div x-show="show" class="fixed inset-0 z-50 flex items-center justify-center px-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-xl" @click="show = false"></div>
    <div class="relative w-full max-w-sm rounded-xl bg-white/15 backdrop-blur-2xl p-4 border border-white/20 shadow-2xl">
        <h2 class="mb-4 text-lg font-semibold text-white">{{ $title }}</h2>
        {{ $slot }}
    </div>
</div> 