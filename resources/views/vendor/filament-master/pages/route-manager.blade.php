<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-primary-50 dark:bg-primary-950/40 border border-primary-200 dark:border-primary-800 rounded-xl p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <x-filament::icon icon="heroicon-o-signal" class="w-6 h-6 text-primary-600" />
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">Route Control Tower</h3>
                    <p class="text-xs text-gray-600 dark:text-gray-400">Search, filter, and inspect all registered application routes.</p>
                </div>
            </div>
            <span class="text-xs font-semibold px-3 py-1 bg-white dark:bg-gray-800 rounded-full border border-primary-200 dark:border-primary-700 text-primary-700 dark:text-primary-300">
                Live Router State
            </span>
        </div>

        {{ $this->table }}

        <!-- Inspection Modal -->
        <x-filament::modal id="route-details-modal" width="3xl">
            <x-slot name="heading">
                Route Inspector
            </x-slot>

            @if ($this->selectedRoute)
                <div class="space-y-4 text-sm">
                    <div class="grid grid-cols-2 gap-4 pb-4 border-b border-gray-200 dark:border-gray-800">
                        <div>
                            <span class="text-xs text-gray-500 uppercase font-semibold">Method & URI</span>
                            <div class="font-mono text-sm mt-1 text-primary-600 dark:text-primary-400 font-bold">
                                {{ $this->selectedRoute['methods'] }} {{ $this->selectedRoute['uri'] }}
                            </div>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 uppercase font-semibold">Route Name</span>
                            <div class="font-mono text-sm mt-1 text-gray-900 dark:text-white">
                                {{ $this->selectedRoute['name'] }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <span class="text-xs text-gray-500 uppercase font-semibold">Action / Handler</span>
                        <div class="font-mono text-xs mt-1 p-2 rounded bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-200">
                            {{ $this->selectedRoute['action'] }}
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-xs text-gray-500 uppercase font-semibold">File Location</span>
                            <div class="font-mono text-xs mt-1 break-all text-gray-700 dark:text-gray-300">
                                {{ $this->selectedRoute['file_path'] }}
                            </div>
                        </div>
                        <div>
                            <span class="text-xs text-gray-500 uppercase font-semibold">Start Line</span>
                            <div class="font-mono text-xs mt-1 text-gray-700 dark:text-gray-300">
                                Line {{ $this->selectedRoute['start_line'] }}
                            </div>
                        </div>
                    </div>

                    @if ($this->selectedRoute['matching_resource'])
                        <div class="p-3 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800">
                            <span class="text-xs text-emerald-800 dark:text-emerald-300 font-semibold flex items-center gap-1.5">
                                <x-filament::icon icon="heroicon-o-check-badge" class="w-4 h-4 text-emerald-600" />
                                Linked Filament Resource
                            </span>
                            <p class="text-xs font-mono text-emerald-700 dark:text-emerald-400 mt-1">
                                {{ $this->selectedRoute['matching_resource'] }}
                            </p>
                        </div>
                    @endif

                    <div>
                        <span class="text-xs text-gray-500 uppercase font-semibold">Middleware Stack</span>
                        <div class="flex flex-wrap gap-1.5 mt-1">
                            @forelse ($this->selectedRoute['middleware'] as $mw)
                                <span class="px-2 py-0.5 text-xs bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded font-mono">
                                    {{ $mw }}
                                </span>
                            @empty
                                <span class="text-xs text-gray-500 italic">None</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif
        </x-filament::modal>
    </div>
</x-filament-panels::page>
