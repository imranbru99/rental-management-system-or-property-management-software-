<?php

namespace App\Services\Buildings;

use App\Enums\PropertyStatus;
use App\Enums\UnitSpaceType;
use App\Models\Property;
use App\Models\PropertyFloor;
use App\Models\PropertyUnit;
use App\Models\UnitSpace;
use Illuminate\Support\Facades\DB;

class BuildingGenerator
{
    /**
     * @param  array{
     *     floors?: int,
     *     units_per_floor?: int,
     *     rooms?: int,
     *     baths?: int,
     *     balconies?: int,
     *     kitchens?: int,
     *     dining?: int,
     *     rent?: int,
     *     deposit?: int
     * }  $options
     */
    public function generate(Property $property, array $options = []): Property
    {
        $floors = max(1, (int) ($options['floors'] ?? $property->floor_count ?: 1));
        $unitsPerFloor = max(1, (int) ($options['units_per_floor'] ?? $property->units_per_floor ?: 1));
        $rooms = max(0, (int) ($options['rooms'] ?? $property->default_rooms));
        $baths = max(0, (int) ($options['baths'] ?? $property->default_baths));
        $balconies = max(0, (int) ($options['balconies'] ?? $property->default_balconies));
        $kitchens = max(0, (int) ($options['kitchens'] ?? $property->default_kitchens));
        $dining = max(0, (int) ($options['dining'] ?? $property->default_dining));
        $rent = (int) ($options['rent'] ?? $property->default_unit_rent);
        $deposit = (int) ($options['deposit'] ?? $property->default_unit_deposit);

        DB::transaction(function () use ($property, $floors, $unitsPerFloor, $rooms, $baths, $balconies, $kitchens, $dining, $rent, $deposit): void {
            $property->units()->each(function (PropertyUnit $unit): void {
                $unit->spaces()->delete();
                $unit->forceDelete();
            });
            $property->floors()->delete();

            for ($level = 1; $level <= $floors; $level++) {
                $floor = PropertyFloor::query()->create([
                    'organization_id' => $property->organization_id,
                    'property_id' => $property->id,
                    'level' => $level,
                    'name' => 'Floor '.$level,
                    'sort_order' => $level,
                ]);

                for ($index = 1; $index <= $unitsPerFloor; $index++) {
                    $code = $level.$this->padUnit($index);
                    $unit = PropertyUnit::query()->create([
                        'organization_id' => $property->organization_id,
                        'property_id' => $property->id,
                        'floor_id' => $floor->id,
                        'name' => 'Unit '.$code,
                        'code' => $code,
                        'sort_order' => $index,
                        'status' => PropertyStatus::Vacant,
                        'bedrooms' => $rooms,
                        'bathrooms' => $baths,
                        'balconies' => $balconies,
                        'kitchens' => $kitchens,
                        'dining' => $dining,
                        'rent' => $rent,
                        'security_deposit' => $deposit,
                    ]);

                    $this->createSpaces($unit, $rooms, $baths, $balconies, $kitchens, $dining);
                }
            }

            $property->forceFill([
                'floor_count' => $floors,
                'units_per_floor' => $unitsPerFloor,
                'default_rooms' => $rooms,
                'default_baths' => $baths,
                'default_balconies' => $balconies,
                'default_kitchens' => $kitchens,
                'default_dining' => $dining,
                'default_unit_rent' => $rent,
                'default_unit_deposit' => $deposit,
                'bedrooms' => $rooms,
                'bathrooms' => $baths,
                'base_rent' => $rent,
                'security_deposit' => $deposit,
                'status' => PropertyStatus::Active,
            ])->save();
        });

        return $property->refresh();
    }

    public function recount(Property $property): void
    {
        $property->units->each(fn (PropertyUnit $unit) => $this->syncSpacesFromCounts($unit));

        $property->forceFill([
            'floor_count' => $property->floors()->count(),
            'units_per_floor' => (int) $property->floors()->withCount('units')->get()->avg('units_count'),
        ])->save();
    }

    public function syncSpacesFromCounts(PropertyUnit $unit): void
    {
        $this->alignSpaces($unit, UnitSpaceType::Room, max(0, (int) $unit->bedrooms), 'Room');
        $this->alignSpaces($unit, UnitSpaceType::Dining, max(0, (int) $unit->dining), 'Dining');
        $this->alignSpaces($unit, UnitSpaceType::Kitchen, max(0, (int) $unit->kitchens), 'Kitchen');
        $this->alignSpaces($unit, UnitSpaceType::Bath, max(0, (int) $unit->bathrooms), 'Bath');
        $this->alignSpaces($unit, UnitSpaceType::Balcony, max(0, (int) $unit->balconies), 'Balcony');

        $unit->syncSpaceCounts();
    }

    public function seedDefaultSpaces(PropertyUnit $unit): void
    {
        if ($unit->spaces()->exists()) {
            return;
        }

        $property = $unit->property;

        $this->createSpaces(
            $unit,
            max(0, (int) ($unit->bedrooms ?: $property?->default_rooms)),
            max(0, (int) ($unit->bathrooms ?: $property?->default_baths)),
            max(0, (int) ($unit->balconies ?: $property?->default_balconies)),
            max(0, (int) ($unit->kitchens ?: $property?->default_kitchens)),
            max(0, (int) ($unit->dining ?: $property?->default_dining)),
        );

        $unit->syncSpaceCounts();
    }

    protected function createSpaces(PropertyUnit $unit, int $rooms, int $baths, int $balconies, int $kitchens, int $dining): void
    {
        $this->alignSpaces($unit, UnitSpaceType::Room, $rooms, 'Room');
        $this->alignSpaces($unit, UnitSpaceType::Dining, $dining, 'Dining');
        $this->alignSpaces($unit, UnitSpaceType::Kitchen, $kitchens, 'Kitchen');
        $this->alignSpaces($unit, UnitSpaceType::Bath, $baths, 'Bath');
        $this->alignSpaces($unit, UnitSpaceType::Balcony, $balconies, 'Balcony');
    }

    protected function alignSpaces(PropertyUnit $unit, UnitSpaceType $type, int $wanted, string $label): void
    {
        $spaces = $unit->spaces()->where('type', $type)->orderBy('sort_order')->get();

        if ($spaces->count() > $wanted) {
            $spaces->slice($wanted)->each->delete();
        }

        $order = (int) ($unit->spaces()->max('sort_order') ?? 0);

        for ($index = $spaces->count() + 1; $index <= $wanted; $index++) {
            $name = $wanted === 1 && ! in_array($type, [UnitSpaceType::Room, UnitSpaceType::Bath], true)
                ? $label
                : $label.' '.$index;

            $this->space($unit, $type, $name, ++$order);
        }
    }

    protected function space(PropertyUnit $unit, UnitSpaceType $type, string $name, int $order): void
    {
        UnitSpace::query()->create([
            'organization_id' => $unit->organization_id,
            'unit_id' => $unit->id,
            'type' => $type,
            'name' => $name,
            'sort_order' => $order,
        ]);
    }

    protected function padUnit(int $index): string
    {
        return str_pad((string) $index, 2, '0', STR_PAD_LEFT);
    }
}
