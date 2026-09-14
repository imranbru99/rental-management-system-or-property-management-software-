<?php

namespace App\Filament\Owner\Pages;

use App\Services\Ai\SupportChatService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class AiAssistant extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::Sparkles;

    protected static string|\UnitEnum|null $navigationGroup = 'Help';

    protected static ?string $title = 'AI assistant';

    protected static ?string $navigationLabel = 'AI assistant';

    protected string $view = 'filament.owner.pages.ai-assistant';

    protected Width|string|null $maxContentWidth = Width::FiveExtraLarge;

    public string $question = '';

    /**
     * @var list<array{role: string, body: string}>
     */
    public array $messages = [];

    public function getHeading(): string
    {
        return 'AI assistant';
    }

    public function getSubheading(): ?string
    {
        return 'Ask how to collect rent, write a lease, assign maintenance, or read a report. Answers stay on this page.';
    }

    /**
     * @return list<string>
     */
    public function suggestions(): array
    {
        return [
            'How do I collect rent in RentOS?',
            'Walk me through approving an application',
            'When should I charge a late fee?',
            'How do I assign a plumbing ticket?',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clear')
                ->label('Clear chat')
                ->icon(Heroicon::OutlinedTrash)
                ->color('gray')
                ->visible(fn (): bool => $this->messages !== [])
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->reset(['question', 'messages']);
                }),
        ];
    }

    public function useSuggestion(string $prompt): void
    {
        $this->question = $prompt;
        $this->ask();
    }

    public function ask(): void
    {
        $prompt = trim($this->question);

        if ($prompt === '') {
            Notification::make()
                ->title('Type a question first')
                ->warning()
                ->send();

            return;
        }

        $this->messages[] = [
            'role' => 'user',
            'body' => $prompt,
        ];

        $answer = app(SupportChatService::class)->answer($prompt, 'owner');

        $this->messages[] = [
            'role' => 'assistant',
            'body' => $answer,
        ];

        $this->question = '';
    }
}
