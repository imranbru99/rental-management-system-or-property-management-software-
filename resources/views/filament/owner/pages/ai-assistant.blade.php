<x-filament-panels::page>
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <x-filament::section
                heading="Conversation"
                description="RentOS answers using your AI Hub provider when a key is set, or a built-in fallback if it is not."
                :icon="\Filament\Support\Icons\Heroicon::Sparkles"
            >

                <div class="min-h-80 max-h-[32rem] space-y-4 overflow-y-auto pr-1">
                    @forelse ($messages as $message)
                        @if ($message['role'] === 'user')
                            <div class="flex justify-end">
                                <div class="max-w-[85%] rounded-2xl rounded-br-md bg-primary-600 px-4 py-3 text-sm leading-6 text-white shadow-sm">
                                    {{ $message['body'] }}
                                </div>
                            </div>
                        @else
                            <div class="flex justify-start gap-3">
                                <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-50 text-primary-700 ring-1 ring-primary-100 dark:bg-primary-400/10 dark:text-primary-300 dark:ring-primary-400/20">
                                    <x-filament::icon :icon="\Filament\Support\Icons\Heroicon::Sparkles" class="h-4 w-4" />
                                </div>
                                <div class="max-w-[85%] rounded-2xl rounded-bl-md bg-gray-50 px-4 py-3 text-sm leading-6 text-gray-800 ring-1 ring-gray-950/5 dark:bg-white/5 dark:text-gray-100 dark:ring-white/10">
                                    {!! nl2br(e($message['body'])) !!}
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="flex h-72 flex-col items-center justify-center rounded-xl border border-dashed border-gray-300 bg-gray-50/70 px-6 text-center dark:border-white/10 dark:bg-white/5">
                            <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-primary-50 text-primary-700 dark:bg-primary-400/10 dark:text-primary-300">
                                <x-filament::icon :icon="\Filament\Support\Icons\Heroicon::Sparkles" class="h-6 w-6" />
                            </div>
                            <p class="text-sm font-medium text-gray-950 dark:text-white">How can RentOS help today?</p>
                            <p class="mt-1 max-w-md text-sm text-gray-500 dark:text-gray-400">
                                Ask about rent collection, lease activation, late fees, or assigning a maintenance vendor.
                            </p>
                        </div>
                    @endforelse
                </div>
            </x-filament::section>

            <x-filament::section>
                <form wire:submit="ask" class="space-y-4">
                    <label class="text-sm font-medium text-gray-950 dark:text-white" for="rentos-ai-question">
                        Your question
                    </label>

                    <x-filament::input.wrapper>
                        <textarea
                            id="rentos-ai-question"
                            wire:model="question"
                            rows="4"
                            placeholder="Example: How do I turn an approved application into an active lease?"
                            class="block w-full border-none bg-transparent px-3 py-2.5 text-base text-gray-950 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6 dark:text-white dark:placeholder:text-gray-500"
                        ></textarea>
                    </x-filament::input.wrapper>

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Press Ask to send. Conversation stays on this screen only.
                        </p>

                        <x-filament::button type="submit" icon="heroicon-m-paper-airplane">
                            <span wire:loading.remove wire:target="ask">Ask RentOS</span>
                            <span wire:loading wire:target="ask">Thinking…</span>
                        </x-filament::button>
                    </div>
                </form>
            </x-filament::section>
        </div>

        <div class="space-y-6">
            <x-filament::section
                heading="Suggested questions"
                description="Click one to send it immediately."
            >

                <div class="flex flex-col gap-2">
                    @foreach ($this->suggestions() as $suggestion)
                        <button
                            type="button"
                            wire:click="useSuggestion({{ \Illuminate\Support\Js::from($suggestion) }})"
                            class="rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-left text-sm text-gray-700 transition hover:border-primary-300 hover:bg-primary-50 hover:text-primary-800 dark:border-white/10 dark:bg-white/5 dark:text-gray-200 dark:hover:border-primary-400/40 dark:hover:bg-primary-400/10"
                        >
                            {{ $suggestion }}
                        </button>
                    @endforeach
                </div>
            </x-filament::section>

            <x-filament::section heading="Tips">
                <ul class="list-disc space-y-2 pl-4 text-sm text-gray-600 dark:text-gray-300">
                    <li>Add a Gemini or OpenAI key in AI Hub for live answers.</li>
                    <li>Ask about a specific screen, such as Applications or Invoices.</li>
                    <li>AI does not change data — it only explains how RentOS works.</li>
                </ul>
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>
