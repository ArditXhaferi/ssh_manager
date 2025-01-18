<div
    x-data="{ 
        show: false,
        title: '',
        message: '',
        type: 'success',
        showNotification(detail) {
            this.title = detail.title || '';
            this.message = detail.message || '';
            this.type = detail.type || 'success';
            this.show = true;
            setTimeout(() => this.show = false, 3000);
        }
    }"
    @notify.window="showNotification($event.detail[0])"
>
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-2"
        class="fixed top-4 inset-x-4 z-[60] md:top-4 md:right-4 md:left-auto md:w-80 rounded-xl bg-[#1C1C1E]/80 backdrop-blur-xl border border-white/10 shadow-2xl"
        x-cloak
    >
        <div class="flex items-start p-4">
            <div class="flex-shrink-0 pt-0.5">
                <template x-if="type === 'success'">
                    <div class="h-10 w-10 rounded-full bg-green-500/10 flex items-center justify-center">
                        <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </template>
                <template x-if="type === 'error'">
                    <div class="h-10 w-10 rounded-full bg-red-500/10 flex items-center justify-center">
                        <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                    </div>
                </template>
            </div>
            <div class="ml-3 w-0 flex-1">
                <p 
                    x-show="title" 
                    class="text-sm font-medium text-white" 
                    x-text="title"
                ></p>
                <p 
                    x-show="message" 
                    class="mt-1 text-sm text-gray-400" 
                    x-text="message"
                ></p>
            </div>
            <div class="ml-4 flex flex-shrink-0">
                <button
                    @click="show = false"
                    class="inline-flex text-gray-400 hover:text-gray-300"
                >
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div> 