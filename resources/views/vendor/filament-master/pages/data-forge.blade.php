<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit.prevent="saveFiles">
            {{ $this->form }}

            <div class="mt-6 flex flex-wrap gap-3">
                <x-filament::button type="button" color="gray" wire:click="generatePreviews">
                    Refresh Preview
                </x-filament::button>

                <x-filament::button type="submit" color="primary" icon="heroicon-o-arrow-down-tray">
                    Save Factory & Seeder Files
                </x-filament::button>

                <x-filament::button type="button" color="success" icon="heroicon-o-play" wire:click="seedNow">
                    Save & Seed Live Records Now
                </x-filament::button>
            </div>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-sparkles" class="w-5 h-5 text-primary-500" />
                    Generated Factory Code
                </h3>
                <pre class="p-4 rounded-lg bg-gray-950 text-emerald-400 font-mono text-xs overflow-x-auto leading-relaxed max-h-96">{{ $this->previewFactory }}</pre>
            </div>

            <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-circle-stack" class="w-5 h-5 text-info-500" />
                    Generated Seeder Code
                </h3>
                <pre class="p-4 rounded-lg bg-gray-950 text-sky-400 font-mono text-xs overflow-x-auto leading-relaxed max-h-96">{{ $this->previewSeeder }}</pre>
            </div>
        </div>
    </div>
</x-filament-panels::page>
