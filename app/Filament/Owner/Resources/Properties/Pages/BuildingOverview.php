<?php

namespace App\Filament\Owner\Resources\Properties\Pages;

use App\Enums\PropertyStatus;
use App\Filament\Owner\Actions\InviteTenantAction;
use App\Filament\Owner\Resources\Properties\PropertyResource;
use App\Models\Property;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class BuildingOverview extends Page
{
    use InteractsWithRecord;

    protected static string $resource = PropertyResource::class;

    protected string $view = 'filament.owner.resources.properties.pages.building-overview';

    protected static bool $shouldRegisterNavigation = false;

    protected Width|string|null $maxContentWidth = Width::Full;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->getRecord()->load([
            'floors.units.spaces',
            'floors.units.tenant',
        ]);
    }

    public function getTitle(): string|Htmlable
    {
        return $this->getRecord()->name;
    }

    public function getHeading(): string|Htmlable
    {
        return 'Building overview';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return $this->getRecord()->full_address;
    }

    public function getRecord(): Property
    {
        /** @var Property $record */
        $record = $this->record;

        return $record;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function unitCards(): array
    {
        $currency = $this->getRecord()->currency ?: 'BDT';
        $cards = [];

        foreach ($this->getRecord()->floors as $floor) {
            foreach ($floor->units as $unit) {
                $status = $unit->status instanceof PropertyStatus ? $unit->status : PropertyStatus::Vacant;
                $spaces = $unit->spaces->groupBy(fn ($space) => $space->type?->value ?? 'other');

                $cards[$unit->id] = [
                    'id' => $unit->id,
                    'code' => $unit->code ?: $unit->name,
                    'name' => $unit->name,
                    'floor' => $floor->name,
                    'status' => $status->value,
                    'status_label' => $status->getLabel(),
                    'occupied' => $status === PropertyStatus::Occupied,
                    'rent' => Money::format((int) $unit->rent, $currency),
                    'deposit' => Money::format((int) $unit->security_deposit, $currency),
                    'tenant' => $unit->tenant?->name,
                    'phone' => $unit->tenant?->phone,
                    'rooms' => $spaces->get('room')?->count() ?? (int) $unit->bedrooms,
                    'dining' => $spaces->get('dining')?->count() ?? (int) $unit->dining,
                    'kitchens' => $spaces->get('kitchen')?->count() ?? (int) $unit->kitchens,
                    'baths' => $spaces->get('bath')?->count() ?? (int) $unit->bathrooms,
                    'balconies' => $spaces->get('balcony')?->count() ?? (int) $unit->balconies,
                ];
            }
        }

        return $cards;
    }

    /**
     * @return array{floors: int, units: int, rented: int, vacant: int, rent: string, collected: string}
     */
    public function stats(): array
    {
        $property = $this->getRecord();
        $currency = $property->currency ?: 'BDT';
        $units = $property->totalUnits();
        $rented = $property->occupiedUnits();

        return [
            'floors' => $property->floors->count(),
            'units' => $units,
            'rented' => $rented,
            'vacant' => max(0, $units - $rented),
            'rent' => Money::format($property->totalMonthlyRent(), $currency),
            'collected' => Money::format($property->collectedMonthlyRent(), $currency),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('layout')
                ->label('Edit layout')
                ->icon(Heroicon::OutlinedSquares2x2)
                ->url(PropertyResource::getUrl('layout', ['record' => $this->getRecord()])),
            InviteTenantAction::make($this->getRecord()),
            Action::make('back')
                ->label('All buildings')
                ->color('gray')
                ->icon(Heroicon::OutlinedArrowLeft)
                ->url(PropertyResource::getUrl()),
        ];
    }
}
