<?php

namespace Tests\Feature;

use App\Enums\PropertyStatus;
use App\Models\Property;
use App\Models\PropertyUnit;
use App\Models\UnitSpace;
use App\Models\User;
use App\Services\Buildings\BuildingGenerator;
use App\Services\Tenants\TenantInviteService;
use App\Support\Money;
use Database\Seeders\RentosDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BuildingSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RentosDemoSeeder::class);
    }

    public function test_generator_builds_floors_units_and_spaces(): void
    {
        $property = Property::query()->where('name', 'Gulshan Studio 4A')->firstOrFail();

        app(BuildingGenerator::class)->generate($property, [
            'floors' => 10,
            'units_per_floor' => 5,
            'rooms' => 3,
            'baths' => 3,
            'balconies' => 1,
            'dining' => 1,
            'kitchens' => 1,
            'rent' => Money::fromMajor(35000),
            'deposit' => Money::fromMajor(70000),
        ]);

        $property->refresh();

        $this->assertSame(10, $property->floors()->count());
        $this->assertSame(50, $property->units()->count());
        $this->assertSame(50 * 9, $property->units()->withCount('spaces')->get()->sum('spaces_count'));
        $this->assertTrue($property->units()->first()->spaces()->where('type', 'dining')->exists());
        $this->assertTrue($property->units()->first()->spaces()->where('type', 'kitchen')->exists());
        $this->assertSame(50 * Money::fromMajor(35000), $property->totalMonthlyRent());
        $this->assertTrue($property->units()->where('code', '101')->exists());
        $this->assertTrue($property->units()->where('code', '1005')->exists());
    }

    public function test_owner_can_invite_existing_tenant_with_generated_password(): void
    {
        $unit = PropertyUnit::query()->whereNull('tenant_id')->orderBy('code')->firstOrFail();
        $password = TenantInviteService::generatePassword();

        $result = app(TenantInviteService::class)->invite($unit, [
            'name' => 'Jamal Hossain',
            'phone' => '01711009988',
            'email' => 'jamal@rentos.test',
            'password' => $password,
            'create_lease' => true,
        ]);

        $this->assertTrue(Hash::check($password, $result['user']->password));
        $this->assertSame(PropertyStatus::Occupied, $unit->fresh()->status);
        $this->assertSame($result['user']->id, $unit->fresh()->tenant_id);
        $this->assertNotNull($result['lease']);
        $this->assertTrue($result['user']->organizations()->whereKey($unit->organization_id)->exists());
    }

    public function test_owner_can_open_building_wizard_and_layout(): void
    {
        $owner = User::query()->where('email', 'owner@rentos.test')->firstOrFail();
        $org = $owner->organizations()->firstOrFail();
        $property = $org->properties()->firstOrFail();

        $this->actingAs($owner)
            ->get('/owner/'.$org->slug.'/properties/create')
            ->assertOk()
            ->assertSee('Add a building')
            ->assertSee('How many floors?')
            ->assertSee('Dining in each unit')
            ->assertSee('Kitchen in each unit');

        $this->actingAs($owner)
            ->get('/owner/'.$org->slug.'/properties/'.$property->slug.'/layout')
            ->assertOk()
            ->assertSee('Floors, units & rooms')
            ->assertSee('Click a unit card')
            ->assertSee('Add existing tenant');

        $this->actingAs($owner)
            ->get('/owner/'.$org->slug.'/properties/'.$property->slug.'/overview')
            ->assertOk()
            ->assertSee('Building overview')
            ->assertSee('All units');
    }

    public function test_demo_buildings_have_generated_units(): void
    {
        $property = Property::query()->where('name', 'Dhanmondi Lake Residences')->firstOrFail();

        $this->assertSame(4, $property->floors()->count());
        $this->assertSame(12, $property->units()->count());
        $this->assertTrue(UnitSpace::query()->where('unit_id', $property->units()->first()->id)->exists());
        $this->assertSame(1, $property->occupiedUnits());
    }
}
