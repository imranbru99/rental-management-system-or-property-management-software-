<?php

namespace App\Filament\Owner\Resources\Conversations;

use App\Filament\Owner\Resources\Conversations\Pages\ManageConversations;
use App\Models\Conversation;
use App\Services\Messaging\MessagingService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ConversationResource extends Resource
{
    protected static ?string $model = Conversation::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|\UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Inbox';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('subject')->required(),
            Select::make('property_id')->relationship('property', 'name')->searchable(),
            Select::make('lease_id')->relationship('lease', 'number')->searchable(),
            Select::make('participants')->relationship('participants', 'name')->multiple()->searchable(),
            Textarea::make('first_message')->required()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject')->searchable(),
                TextColumn::make('property.name'),
                TextColumn::make('lease.number'),
                TextColumn::make('messages_count')->counts('messages')->label('Messages'),
                TextColumn::make('last_message_at')->since(),
            ])
            ->defaultSort('last_message_at', 'desc')
            ->recordActions([
                Action::make('reply')
                    ->form([
                        Textarea::make('body')->required(),
                    ])
                    ->action(function (Conversation $record, array $data, MessagingService $messages): void {
                        $messages->reply($record, auth()->user(), $data['body']);
                        Notification::make()->title('Message sent')->success()->send();
                    }),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageConversations::route('/'),
        ];
    }
}
