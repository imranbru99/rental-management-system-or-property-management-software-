<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit.prevent="saveWidgets">
            {{ $this->form }}

            <div class="mt-6 flex gap-3">
                <x-filament::button type="button" color="gray" wire:click="generatePreviews">
                    Refresh Preview
                </x-filament::button>

                <x-filament::button type="submit" color="primary" icon="heroicon-o-arrow-down-tray">
                    Generate Widgets to app/Filament/Widgets/
                </x-filament::button>
            </div>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-presentation-chart-line" class="w-5 h-5 text-primary-500" />
                    Stats Overview Widget
                </h3>
                <pre class="p-4 rounded-lg bg-gray-950 text-emerald-400 font-mono text-xs overflow-x-auto leading-relaxed max-h-96">{{ $this->previewStatsWidget }}</pre>
            </div>

            <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-chart-pie" class="w-5 h-5 text-info-500" />
                    Growth Chart Widget
                </h3>
                <pre class="p-4 rounded-lg bg-gray-950 text-sky-400 font-mono text-xs overflow-x-auto leading-relaxed max-h-96">{{ $this->previewChartWidget }}</pre>
            </div>
        </div>
    </div>
</x-filament-panels::page>
