<?php

namespace App\Filament\Owner\Resources\Properties\Pages;

use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Filament\Owner\Resources\Properties\PropertyResource;
use App\Services\Buildings\BuildingGenerator;
use App\Support\Money;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Icons\Heroicon;

class CreateBuilding extends CreateRecord
{
    use HasWizard;

    protected static string $resource = PropertyResource::class;

    public function getTitle(): string
    {
        return 'Add a building';
    }

    /**
     * @return array<Step>
     */
    public function getSteps(): array
    {
        return [
            Step::make('Building')
                ->description('Name the house. That is enough to start.')
                ->icon(Heroicon::OutlinedHomeModern)
                ->schema([
                    TextInput::make('name')
                        ->label('Building / house name')
                        ->placeholder('Rahman Tower, Dhanmondi')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    TextInput::make('address_line')->label('Street address')->placeholder('Optional — we can use the building name'),
                    TextInput::make('city')->default('Dhaka'),
                    TextInput::make('state')->label('Area / state'),
                    Select::make('type')->options(PropertyType::class)->default(PropertyType::Apartment)->required(),
                    TextInput::make('country')->default('BD')->required()->maxLength(8),
                    TextInput::make('currency')->default('BDT')->required()->maxLength(8),
                ])->columns(2),

            Step::make('Floors & units')
                ->description('Tell us how the building is shaped. RentOS generates every unit.')
                ->icon(Heroicon::OutlinedBuildingOffice2)
                ->schema([
                    TextInput::make('floor_count')
                        ->label('How many floors?')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(80)
                        ->default(10)
                        ->required()
                        ->live(),
                    TextInput::make('units_per_floor')
                        ->label('Units on each floor')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(30)
                        ->default(5)
                        ->required()
                        ->live(),
                    TextInput::make('default_rooms')
                        ->label('Rooms in each unit')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(12)
                        ->default(3)
                        ->required()
                        ->live(),
                    TextInput::make('default_baths')
                        ->label('Baths in each unit')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(12)
                        ->default(3)
                        ->required()
                        ->live(),
                    TextInput::make('default_balconies')
                        ->label('Balconies in each unit')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(8)
                        ->default(1)
                        ->required()
                        ->live(),
                    TextInput::make('default_dining')
                        ->label('Dining in each unit')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(4)
                        ->default(1)
                        ->required()
                        ->live(),
                    TextInput::make('default_kitchens')
                        ->label('Kitchen in each unit')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(4)
                        ->default(1)
                        ->required()
                        ->live(),
                    TextInput::make('rent_major')
                        ->label('Monthly rent per unit')
                        ->numeric()
                        ->minValue(0)
                        ->default(35000)
                        ->prefix('৳')
                        ->helperText('Same starting rent for every unit. You can change any unit later.')
                        ->required()
                        ->live(),
                    TextInput::make('deposit_major')
                        ->label('Security deposit per unit')
                        ->numeric()
                        ->minValue(0)
                        ->default(70000)
                        ->prefix('৳')
                        ->required()
                        ->live(),
                    Placeholder::make('preview')
                        ->label('Auto totals')
                        ->content(fn (Get $get): string => self::preview($get))
                        ->columnSpanFull(),
                ])->columns(2),

            Step::make('Review')
                ->description('Create the building. Then drag, drop, or delete any floor, unit, room, dining, kitchen, bath, or balcony.')
                ->icon(Heroicon::OutlinedClipboardDocumentCheck)
                ->schema([
                    Placeholder::make('summary')
                        ->label('What we will create')
                        ->content(fn (Get $get): string => self::preview($get).' You can edit every floor and unit on the next screen.')
                        ->columnSpanFull(),
                    Select::make('status')
                        ->options(PropertyStatus::class)
                        ->default(PropertyStatus::Active)
                        ->required(),
                ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['default_unit_rent'] = Money::fromMajor($data['rent_major'] ?? 0);
        $data['default_unit_deposit'] = Money::fromMajor($data['deposit_major'] ?? 0);
        $data['base_rent'] = $data['default_unit_rent'];
        $data['security_deposit'] = $data['default_unit_deposit'];
        $data['bedrooms'] = (int) ($data['default_rooms'] ?? 3);
        $data['bathrooms'] = (int) ($data['default_baths'] ?? 3);
        $data['address_line'] = $data['address_line'] ?: $data['name'];
        $data['city'] = $data['city'] ?: 'Dhaka';
        unset($data['rent_major'], $data['deposit_major'], $data['preview'], $data['summary']);

        return $data;
    }

    protected function afterCreate(): void
    {
        app(BuildingGenerator::class)->generate($this->record);
    }

    protected function getRedirectUrl(): string
    {
        return PropertyResource::getUrl('layout', ['record' => $this->record]);
    }

    protected static function preview(Get $get): string
    {
        $floors = max(0, (int) $get('floor_count'));
        $perFloor = max(0, (int) $get('units_per_floor'));
        $rooms = (int) $get('default_rooms');
        $baths = (int) $get('default_baths');
        $balconies = (int) $get('default_balconies');
        $dining = (int) $get('default_dining');
        $kitchens = (int) $get('default_kitchens');
        $rent = (float) $get('rent_major');
        $units = $floors * $perFloor;
        $monthly = $units * $rent;

        return sprintf(
            '%d floors × %d units = %d units. Each unit: %d room, %d dining, %d kitchen, %d bath, %d balcony. Potential rent %s / month.',
            $floors,
            $perFloor,
            $units,
            $rooms,
            $dining,
            $kitchens,
            $baths,
            $balconies,
            Money::format(Money::fromMajor($monthly), (string) ($get('currency') ?: 'BDT'))
        );
    }
}
