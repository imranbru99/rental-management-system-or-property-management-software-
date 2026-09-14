<?php

namespace App\Filament\Owner\Resources\Documents;

use App\Enums\DocumentType;
use App\Filament\Owner\Resources\Documents\Pages\ManageDocuments;
use App\Models\Document;
use App\Models\Lease;
use App\Models\Property;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    protected static string|\UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 40;

    protected static ?string $navigationLabel = 'Document vault';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required(),
            Select::make('type')->options(DocumentType::class)->required(),
            Select::make('documentable_type')
                ->label('Attached to')
                ->options([
                    Property::class => 'Building',
                    Lease::class => 'Lease',
                ])
                ->required()
                ->live(),
            Select::make('documentable_id')
                ->label('Record')
                ->options(function (callable $get) {
                    $orgId = Filament::getTenant()?->getKey();

                    return match ($get('documentable_type')) {
                        Property::class => Property::query()->when($orgId, fn ($query) => $query->where('organization_id', $orgId))->pluck('name', 'id'),
                        Lease::class => Lease::query()->when($orgId, fn ($query) => $query->where('organization_id', $orgId))->pluck('number', 'id'),
                        default => [],
                    };
                })
                ->required()
                ->searchable(),
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
                TextColumn::make('documentable_type')->label('Attached to')->formatStateUsing(
                    fn (?string $state) => class_basename((string) $state)
                ),
                TextColumn::make('expires_on')->date()->placeholder('—'),
                TextColumn::make('uploader.name')->label('Uploaded by'),
            ])
            ->filters([
                SelectFilter::make('type')->options(DocumentType::class),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageDocuments::route('/'),
        ];
    }
}
