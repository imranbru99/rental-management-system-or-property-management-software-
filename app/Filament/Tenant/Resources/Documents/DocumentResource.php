<?php

namespace App\Filament\Tenant\Resources\Documents;

use App\Enums\DocumentType;
use App\Filament\Tenant\Resources\Documents\Pages\ManageDocuments;
use App\Models\Document;
use App\Models\Lease;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    protected static string|\UnitEnum|null $navigationGroup = 'Home';

    public static function getEloquentQuery(): Builder
    {
        $leaseIds = auth()->user()?->leases()->pluck('leases.id') ?? collect();

        return parent::getEloquentQuery()
            ->where(function (Builder $query) use ($leaseIds): void {
                $query->where('uploaded_by', auth()->id())
                    ->orWhere(function (Builder $inner) use ($leaseIds): void {
                        $inner->where('documentable_type', Lease::class)
                            ->whereIn('documentable_id', $leaseIds);
                    });
            });
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required(),
            Select::make('type')->options(DocumentType::class)->required(),
            Select::make('documentable_id')
                ->label('Lease')
                ->options(fn () => auth()->user()?->leases()->pluck('leases.number', 'leases.id') ?? [])
                ->required(),
            FileUpload::make('path')->disk('public')->directory('documents')->required(),
            DatePicker::make('expires_on'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('type')->badge(),
                TextColumn::make('expires_on')->date(),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->visible(fn (Document $record) => $record->uploaded_by === auth()->id()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageDocuments::route('/'),
        ];
    }
}
