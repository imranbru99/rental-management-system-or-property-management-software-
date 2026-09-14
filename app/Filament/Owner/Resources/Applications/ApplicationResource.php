<?php

namespace App\Filament\Owner\Resources\Applications;

use App\Enums\ApplicationStatus;
use App\Filament\Owner\Resources\Applications\Pages\ManageApplications;
use App\Models\Application;
use App\Services\Ai\ScreeningAiService;
use App\Services\Leasing\LeaseService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ApplicationResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'Leasing';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('listing_id')->relationship('listing', 'title')->required()->searchable(),
            Select::make('property_id')->relationship('property', 'name')->required()->searchable(),
            Select::make('applicant_id')->relationship('applicant', 'name')->required()->searchable(),
            Select::make('status')->options(ApplicationStatus::class)->required(),
            TextInput::make('monthly_income')->numeric(),
            TextInput::make('employer'),
            TextInput::make('occupants')->numeric()->default(1),
            Toggle::make('has_pets'),
            TextInput::make('risk_score')->numeric(),
            Textarea::make('risk_notes')->columnSpanFull(),
            Textarea::make('reviewer_notes')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')->searchable(),
                TextColumn::make('applicant.name'),
                TextColumn::make('listing.title')->limit(30),
                TextColumn::make('status')->badge(),
                TextColumn::make('risk_score'),
                TextColumn::make('submitted_at')->since(),
            ])
            ->filters([
                SelectFilter::make('status')->options(ApplicationStatus::class),
            ])
            ->recordActions([
                Action::make('score')
                    ->label('AI screen')
                    ->action(function (Application $record, ScreeningAiService $ai): void {
                        $ai->score($record);
                        Notification::make()->title('Risk score updated')->success()->send();
                    }),
                Action::make('approveLease')
                    ->label('Create lease')
                    ->visible(fn (Application $record) => $record->status !== ApplicationStatus::Approved)
                    ->action(function (Application $record, LeaseService $leases): void {
                        $leases->createFromApplication($record);
                        Notification::make()->title('Draft lease created')->success()->send();
                    }),
                Action::make('reject')
                    ->color('danger')
                    ->visible(fn (Application $record) => ! in_array($record->status, [ApplicationStatus::Approved, ApplicationStatus::Rejected], true))
                    ->form([Textarea::make('reviewer_notes')->required()])
                    ->action(function (Application $record, array $data): void {
                        $record->update([
                            'status' => ApplicationStatus::Rejected,
                            'reviewer_notes' => $data['reviewer_notes'],
                            'reviewed_at' => now(),
                            'reviewed_by' => auth()->id(),
                        ]);
                        Notification::make()->title('Application rejected')->success()->send();
                    }),
                Action::make('uploadDocument')
                    ->form([
                        Select::make('type')->options([
                            'id' => 'ID',
                            'income_proof' => 'Income proof',
                            'other' => 'Other',
                        ])->required(),
                        FileUpload::make('path')->disk('public')->directory('applications')->required(),
                    ])
                    ->action(function (Application $record, array $data): void {
                        $record->documents()->create([
                            'type' => $data['type'],
                            'path' => $data['path'],
                            'original_name' => is_string($data['path']) ? basename($data['path']) : null,
                            'verification_status' => 'pending',
                        ]);
                        Notification::make()->title('Document uploaded')->success()->send();
                    }),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageApplications::route('/'),
        ];
    }
}
