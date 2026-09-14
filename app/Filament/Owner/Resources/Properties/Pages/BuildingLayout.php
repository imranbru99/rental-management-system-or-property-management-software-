<?php

namespace App\Filament\Owner\Resources\Properties\Pages;

use App\Enums\PropertyStatus;
use App\Filament\Owner\Actions\InviteTenantAction;
use App\Filament\Owner\Resources\Properties\PropertyResource;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Services\Buildings\BuildingGenerator;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class BuildingLayout extends EditRecord
{
    protected static string $resource = PropertyResource::class;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.owner.resources.properties.pages.building-layout';

    protected Width|string|null $maxContentWidth = Width::Full;

    public function getTitle(): string|Htmlable
    {
        return $this->getRecord()->name;
    }

    public function getHeading(): string|Htmlable
    {
        return 'Floors, units & rooms';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Click a unit card to edit it. Drag to reorder. Totals update when you save.';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Building')
                    ->columns(3)
                    ->schema([
                        TextInput::make('name')->label('Building name')->required()->maxLength(255),
                        TextInput::make('city')->required(),
                        TextInput::make('currency')->required()->maxLength(8),
                        Placeholder::make('live_totals')
                            ->label('Auto totals')
                            ->content(fn (Get $get, Property $record): string => self::liveTotals($get, $record))
                            ->columnSpanFull(),
                    ]),
                Section::make('Floors')
                    ->description('Open a floor, then click a unit card. The editor opens in place so you can change rent, status, and rooms without the list taking over the page.')
                    ->schema([
                        Repeater::make('floors')
                            ->relationship()
                            ->orderColumn('sort_order')
                            ->reorderable()
                            ->reorderableWithDragAndDrop()
                            ->cloneable()
                            ->collapsible()
                            ->collapsed()
                            ->truncateItemLabel(false)
                            ->extraAttributes(['class' => 'bl-floors'])
                            ->addActionLabel('Add floor')
                            ->itemLabel(fn (?array $state): HtmlString => self::floorItemLabel($state))
                            ->deleteAction(fn (Action $action) => $action
                                ->requiresConfirmation()
                                ->modalHeading('Delete this floor?')
                                ->modalDescription('Every unit on this floor will be removed.'))
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                $data['name'] = $data['name'] ?: ('Floor '.($data['level'] ?? ''));
                                $data['level'] = (int) ($data['level'] ?: 1);

                                return $data;
                            })
                            ->schema([
                                TextInput::make('name')->label('Floor name')->required()->live(onBlur: true),
                                TextInput::make('level')->numeric()->required()->minValue(0)->default(1),
                                Repeater::make('units')
                                    ->relationship()
                                    ->orderColumn('sort_order')
                                    ->reorderable()
                                    ->reorderableWithDragAndDrop()
                                    ->cloneable()
                                    ->collapsible()
                                    ->collapsed()
                                    ->truncateItemLabel(false)
                                    ->extraAttributes(['class' => 'bl-units'])
                                    ->addActionLabel('Add unit')
                                    ->itemLabel(fn (?array $state): HtmlString => self::unitItemLabel($state))
                                    ->deleteAction(fn (Action $action) => $action
                                        ->requiresConfirmation()
                                        ->modalHeading('Delete this unit?'))
                                    ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                        $data['property_id'] = $this->getRecord()->id;
                                        $data['name'] = $data['name'] ?: ('Unit '.($data['code'] ?? 'new'));
                                        $data['status'] ??= PropertyStatus::Vacant->value;
                                        $data['bedrooms'] ??= $this->getRecord()->default_rooms;
                                        $data['bathrooms'] ??= $this->getRecord()->default_baths;
                                        $data['balconies'] ??= $this->getRecord()->default_balconies;
                                        $data['kitchens'] ??= $this->getRecord()->default_kitchens;
                                        $data['dining'] ??= $this->getRecord()->default_dining;

                                        return $data;
                                    })
                                    ->afterCreate(function (PropertyUnit $record): void {
                                        app(BuildingGenerator::class)->seedDefaultSpaces($record);
                                    })
                                    ->schema([
                                        Section::make('Unit details')
                                            ->columns(3)
                                            ->schema([
                                                TextInput::make('code')->label('Unit no.')->required()->maxLength(32)->live(onBlur: true),
                                                TextInput::make('name')->required()->live(onBlur: true),
                                                Select::make('status')
                                                    ->options(PropertyStatus::class)
                                                    ->default(PropertyStatus::Vacant)
                                                    ->required()
                                                    ->live(),
                                                TextInput::make('rent')
                                                    ->label('Monthly rent')
                                                    ->numeric()
                                                    ->prefix('৳')
                                                    ->required()
                                                    ->live(onBlur: true)
                                                    ->formatStateUsing(fn ($state) => $state !== null ? Money::toMajor((int) $state) : 0)
                                                    ->dehydrateStateUsing(fn ($state) => Money::fromMajor($state ?? 0)),
                                                TextInput::make('security_deposit')
                                                    ->label('Deposit')
                                                    ->numeric()
                                                    ->prefix('৳')
                                                    ->required()
                                                    ->formatStateUsing(fn ($state) => $state !== null ? Money::toMajor((int) $state) : 0)
                                                    ->dehydrateStateUsing(fn ($state) => Money::fromMajor($state ?? 0)),
                                            ]),
                                        Section::make('Spaces')
                                            ->description('Set how many of each space this unit has. Example: Room = 3, Kitchen = 1.')
                                            ->extraAttributes(['class' => 'bl-counts'])
                                            ->columns(5)
                                            ->schema([
                                                TextInput::make('bedrooms')
                                                    ->label('Room')
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->default(0)
                                                    ->required()
                                                    ->live(onBlur: true),
                                                TextInput::make('kitchens')
                                                    ->label('Kitchen')
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->default(0)
                                                    ->required()
                                                    ->live(onBlur: true),
                                                TextInput::make('dining')
                                                    ->label('Dining')
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->default(0)
                                                    ->required()
                                                    ->live(onBlur: true),
                                                TextInput::make('bathrooms')
                                                    ->label('Bath')
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->default(0)
                                                    ->required()
                                                    ->live(onBlur: true),
                                                TextInput::make('balconies')
                                                    ->label('Balcony')
                                                    ->numeric()
                                                    ->minValue(0)
                                                    ->default(0)
                                                    ->required()
                                                    ->live(onBlur: true),
                                            ]),
                                    ])
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }

    protected function afterSave(): void
    {
        app(BuildingGenerator::class)->recount($this->getRecord());
    }

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();

        return [
            Action::make('overview')
                ->label('Overview')
                ->icon(Heroicon::OutlinedBuildingOffice2)
                ->url(PropertyResource::getUrl('overview', ['record' => $record])),
            InviteTenantAction::make($record),
            Action::make('generateStructure')
                ->label($record->floors()->exists() ? 'Rebuild floors' : 'Generate floors')
                ->icon(Heroicon::OutlinedSparkles)
                ->color('gray')
                ->schema([
                    TextInput::make('floors')->label('Floors')->numeric()->required()->default($record->floor_count ?: 10),
                    TextInput::make('units_per_floor')->label('Units per floor')->numeric()->required()->default($record->units_per_floor ?: 5),
                    TextInput::make('rooms')->numeric()->required()->default($record->default_rooms ?: 3),
                    TextInput::make('baths')->numeric()->required()->default($record->default_baths ?: 3),
                    TextInput::make('balconies')->numeric()->required()->default($record->default_balconies ?: 1),
                    TextInput::make('dining')->numeric()->required()->default($record->default_dining ?: 1),
                    TextInput::make('kitchens')->label('Kitchens')->numeric()->required()->default($record->default_kitchens ?: 1),
                    TextInput::make('rent_major')->label('Rent per unit')->prefix('৳')->numeric()->required()->default(Money::toMajor((int) ($record->default_unit_rent ?: $record->base_rent))),
                ])
                ->requiresConfirmation()
                ->modalHeading('Generate every floor and unit?')
                ->modalDescription('This replaces the current floors, units, rooms, dining, kitchen, baths, and balconies.')
                ->action(function (array $data): void {
                    app(BuildingGenerator::class)->generate($this->getRecord(), [
                        'floors' => (int) $data['floors'],
                        'units_per_floor' => (int) $data['units_per_floor'],
                        'rooms' => (int) $data['rooms'],
                        'baths' => (int) $data['baths'],
                        'balconies' => (int) $data['balconies'],
                        'dining' => (int) $data['dining'],
                        'kitchens' => (int) $data['kitchens'],
                        'rent' => Money::fromMajor($data['rent_major'] ?? 0),
                        'deposit' => (int) ($this->getRecord()->default_unit_deposit ?: $this->getRecord()->security_deposit),
                    ]);

                    $this->redirect(PropertyResource::getUrl('layout', ['record' => $this->getRecord()]));
                }),
            Action::make('back')
                ->label('All buildings')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->url(PropertyResource::getUrl()),
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): ?string
    {
        return null;
    }

    /**
     * @return array<int, string>
     */
    protected function getSavedNotificationTitle(): ?string
    {
        $record = $this->getRecord()->refresh();

        return $record->totalUnits().' units · '.Money::format($record->totalMonthlyRent(), $record->currency).' / month';
    }

    /**
     * @param  array<string, mixed>|null  $state
     */
    protected static function floorItemLabel(?array $state): HtmlString
    {
        $name = (string) ($state['name'] ?? 'Floor');
        $units = self::floorUnitCount($state);

        return new HtmlString(
            '<span class="bl-floor-label">'.
                '<span class="bl-floor-name">'.e($name).'</span>'.
                '<span class="bl-floor-meta">'.$units.' units</span>'.
            '</span>'
        );
    }

    /**
     * @param  array<string, mixed>|null  $state
     */
    protected static function unitItemLabel(?array $state): HtmlString
    {
        $code = (string) ($state['code'] ?? $state['name'] ?? 'Unit');
        $name = (string) ($state['name'] ?? 'New unit');
        $status = $state['status'] ?? PropertyStatus::Vacant->value;
        $statusValue = $status instanceof PropertyStatus ? $status->value : (string) $status;
        $statusEnum = PropertyStatus::tryFrom($statusValue) ?? PropertyStatus::Vacant;
        $pill = match ($statusEnum) {
            PropertyStatus::Occupied => 'ok',
            PropertyStatus::Vacant => 'wait',
            PropertyStatus::Maintenance => 'fix',
            default => 'idle',
        };

        return new HtmlString(
            '<span class="bl-chip">'.
                '<span class="bl-chip-top">'.
                    '<span class="bl-chip-code">'.e($code).'</span>'.
                    '<span class="bl-pill '.$pill.'">'.e($statusEnum->getLabel()).'</span>'.
                '</span>'.
                '<span class="bl-chip-name">'.e($name).'</span>'.
                '<span class="bl-chip-foot">'.
                    '<span class="bl-chip-rent">'.e(self::unitMoney($state['rent'] ?? 0)).'<small>/mo</small></span>'.
                    '<span class="bl-chip-spaces">'.e(self::unitSpaceSummary($state)).'</span>'.
                '</span>'.
            '</span>'
        );
    }

    /**
     * @param  array<string, mixed>|null  $state
     */
    protected static function floorUnitCount(?array $state): int
    {
        $fromState = is_array($state['units'] ?? null) ? count($state['units']) : 0;

        if (! empty($state['id'])) {
            return max($fromState, PropertyUnit::query()->where('floor_id', $state['id'])->count());
        }

        return $fromState;
    }

    /**
     * @param  array<string, mixed>|null  $state
     */
    protected static function unitSpaceSummary(?array $state): string
    {
        $parts = [];

        foreach ([
            'bedrooms' => 'Room',
            'kitchens' => 'Kitchen',
            'dining' => 'Dining',
            'bathrooms' => 'Bath',
            'balconies' => 'Balcony',
        ] as $key => $label) {
            $count = (int) ($state[$key] ?? 0);

            if ($count > 0) {
                $parts[] = $label.' '.$count;
            }
        }

        if ($parts === [] && ! empty($state['id'])) {
            $unit = PropertyUnit::query()->find($state['id']);

            if ($unit) {
                return self::unitSpaceSummary([
                    'bedrooms' => $unit->bedrooms,
                    'kitchens' => $unit->kitchens,
                    'dining' => $unit->dining,
                    'bathrooms' => $unit->bathrooms,
                    'balconies' => $unit->balconies,
                ]);
            }
        }

        return $parts === [] ? 'No spaces' : implode(' · ', $parts);
    }

    protected static function unitMoney(mixed $amount): string
    {
        $amount = (float) ($amount ?? 0);

        if ($amount >= 100000) {
            return Money::format((int) $amount);
        }

        return Money::format(Money::fromMajor($amount));
    }

    protected static function liveTotals(Get $get, Property $record): string
    {
        $floors = $get('floors') ?? [];
        $floorCount = is_array($floors) ? count($floors) : 0;
        $unitCount = 0;
        $rooms = 0;
        $baths = 0;
        $balconies = 0;
        $dining = 0;
        $kitchens = 0;
        $rentMinor = 0;

        foreach (is_array($floors) ? $floors : [] as $floor) {
            foreach ($floor['units'] ?? [] as $unit) {
                $unitCount++;
                $rentMinor += Money::fromMajor($unit['rent'] ?? 0);
                $rooms += (int) ($unit['bedrooms'] ?? 0);
                $baths += (int) ($unit['bathrooms'] ?? 0);
                $balconies += (int) ($unit['balconies'] ?? 0);
                $dining += (int) ($unit['dining'] ?? 0);
                $kitchens += (int) ($unit['kitchens'] ?? 0);
            }
        }

        return sprintf(
            '%d floors · %d units · %d rooms · %d dining · %d kitchen · %d baths · %d balconies · potential rent %s / month. Occupied now: %d.',
            $floorCount,
            $unitCount,
            $rooms,
            $dining,
            $kitchens,
            $baths,
            $balconies,
            Money::format($rentMinor, $record->currency ?: 'BDT'),
            $record->occupiedUnits()
        );
    }
}
