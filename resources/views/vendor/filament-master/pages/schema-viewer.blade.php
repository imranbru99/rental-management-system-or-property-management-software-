<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 rounded-xl p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <x-filament::icon icon="heroicon-o-share" class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
                <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">Database Entity-Relationship Diagram</h3>
                    <p class="text-xs text-gray-600 dark:text-gray-400">Live generated diagram of database tables, column types, primary keys, and foreign key relations.</p>
                </div>
            </div>
            <x-filament::button type="button" color="gray" wire:click="generateDiagram">
                Reload Graph
            </x-filament::button>
        </div>

        <!-- Mermaid Diagram Display -->
        <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-200 dark:border-gray-800">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-circle-stack" class="w-5 h-5 text-primary-500" />
                    Schema ERD Notation (Mermaid)
                </h3>
            </div>
            <pre class="p-4 rounded-lg bg-gray-950 text-sky-400 font-mono text-xs overflow-x-auto leading-relaxed max-h-96">{{ $this->mermaidCode }}</pre>
        </div>

        <!-- Table Breakdown Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($this->tablesData as $tableName => $columns)
                <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 shadow-sm">
                    <div class="flex items-center justify-between pb-2 mb-3 border-b border-gray-100 dark:border-gray-800">
                        <span class="font-mono font-bold text-sm text-primary-600 dark:text-primary-400">{{ $tableName }}</span>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                            {{ count($columns) }} cols
                        </span>
                    </div>

                    <div class="space-y-1.5 text-xs font-mono">
                        @foreach ($columns as $col)
                            <div class="flex items-center justify-between text-gray-700 dark:text-gray-300">
                                <div class="flex items-center gap-1.5">
                                    @if ($col->isPrimary)
                                        <span class="text-[10px] font-bold text-amber-500 bg-amber-50 dark:bg-amber-950/50 px-1 rounded">PK</span>
                                    @elseif ($col->isForeignKey)
                                        <span class="text-[10px] font-bold text-indigo-500 bg-indigo-50 dark:bg-indigo-950/50 px-1 rounded">FK</span>
                                    @endif
                                    <span>{{ $col->name }}</span>
                                </div>
                                <span class="text-gray-400">{{ $col->type }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
