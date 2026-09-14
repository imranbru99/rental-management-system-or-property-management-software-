<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit.prevent="saveMigration">
            {{ $this->form }}

            <div class="mt-6 flex gap-3">
                <x-filament::button type="button" color="gray" wire:click="generatePreview">
                    Refresh Preview
                </x-filament::button>

                <x-filament::button type="submit" color="primary" icon="heroicon-o-check-circle">
                    Generate & Save Migration
                </x-filament::button>
            </div>
        </form>

        <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-200 dark:border-gray-800">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-code-bracket" class="w-5 h-5 text-primary-500" />
                    Migration Code Preview
                </h3>
                <span class="text-xs font-mono text-gray-500">Live generated from schema</span>
            </div>

            <pre class="p-4 rounded-lg bg-gray-950 text-emerald-400 font-mono text-xs overflow-x-auto leading-relaxed">{{ $this->previewCode }}</pre>
        </div>
    </div>
</x-filament-panels::page>
