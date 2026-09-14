<x-filament-panels::page>
    <div class="space-y-6">
        <form wire:submit.prevent="buildModule">
            {{ $this->form }}

            <div class="mt-6">
                <x-filament::button type="submit" size="xl" color="primary" icon="heroicon-o-sparkles">
                    Build Entire Module (Full Stack)
                </x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
