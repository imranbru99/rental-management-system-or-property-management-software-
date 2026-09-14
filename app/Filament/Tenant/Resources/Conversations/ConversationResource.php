<?php

namespace App\Filament\Tenant\Resources\Conversations;

use App\Filament\Tenant\Resources\Conversations\Pages\ManageConversations;
use App\Models\Conversation;
use App\Services\Messaging\MessagingService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ConversationResource extends Resource
{
    protected static ?string $model = Conversation::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|\UnitEnum|null $navigationGroup = 'Home';

    protected static ?string $navigationLabel = 'Messages';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('participants', fn (Builder $query) => $query->where('users.id', auth()->id()));
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('lease_id')
                ->relationship(
                    'lease',
                    'number',
                    fn (Builder $query) => $query->where('primary_tenant_id', auth()->id())
                )
                ->required(),
            TextInput::make('subject')->required(),
            Textarea::make('first_message')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject')->searchable(),
                TextColumn::make('lease.number'),
                TextColumn::make('messages_count')->counts('messages'),
                TextColumn::make('last_message_at')->since(),
            ])
            ->recordActions([
                Action::make('reply')
                    ->form([Textarea::make('body')->required()])
                    ->action(function (Conversation $record, array $data, MessagingService $messages): void {
                        $messages->reply($record, auth()->user(), $data['body']);
                        Notification::make()->title('Message sent')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageConversations::route('/'),
        ];
    }
}
