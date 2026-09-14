<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit.prevent="generateFiles">
            {{ $this->form }}

            <div class="mt-6 flex gap-3">
                <x-filament::button type="button" color="gray" wire:click="generatePreviews">
                    Refresh Previews
                </x-filament::button>

                <x-filament::button type="submit" color="primary" icon="heroicon-o-arrow-down-tray">
                    Generate Files to Disk
                </x-filament::button>
            </div>
        </form>

        <div class="space-y-6">
            <!-- Controller Preview -->
            <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-3">
                    <x-filament::icon icon="heroicon-o-code-bracket-square" class="w-5 h-5 text-primary-500" />
                    Generated Controller
                </h3>
                <pre class="p-4 rounded-lg bg-gray-950 text-sky-400 font-mono text-xs overflow-x-auto leading-relaxed">{{ $this->previewController }}</pre>
            </div>

            <!-- Form Requests Previews -->
            @if ($this->previewStoreRequest)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Store Form Request</h4>
                        <pre class="p-4 rounded-lg bg-gray-950 text-amber-400 font-mono text-xs overflow-x-auto leading-relaxed max-h-96">{{ $this->previewStoreRequest }}</pre>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm">
                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Update Form Request</h4>
                        <pre class="p-4 rounded-lg bg-gray-950 text-amber-400 font-mono text-xs overflow-x-auto leading-relaxed max-h-96">{{ $this->previewUpdateRequest }}</pre>
                    </div>
                </div>
            @endif

            <!-- API Resource Preview -->
            @if ($this->previewApiResource)
                <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">API Resource (JsonResource)</h4>
                    <pre class="p-4 rounded-lg bg-gray-950 text-emerald-400 font-mono text-xs overflow-x-auto leading-relaxed">{{ $this->previewApiResource }}</pre>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
