<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\User;
use Database\Seeders\RentosDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentosSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RentosDemoSeeder::class);
    }

    public function test_public_home_renders(): void
    {
        $this->get('/')->assertOk()->assertSee('RentOS');
    }

    public function test_listings_api_returns_published_homes(): void
    {
        $this->getJson('/api/listings')->assertOk()->assertJsonStructure(['data']);
    }

    public function test_owner_can_open_properties(): void
    {
        $owner = User::query()->where('email', 'owner@rentos.test')->firstOrFail();
        $org = $owner->organizations()->firstOrFail();

        $this->actingAs($owner)
            ->get('/owner/'.$org->slug.'/properties')
            ->assertOk();
    }

    public function test_tenant_can_open_invoices(): void
    {
        $tenant = User::query()->where('email', 'tenant@rentos.test')->firstOrFail();

        $this->actingAs($tenant)
            ->get('/tenant/invoices')
            ->assertOk();
    }

    public function test_admin_can_open_organizations(): void
    {
        $admin = User::query()->where('email', 'admin@rentos.test')->firstOrFail();

        $this->actingAs($admin)
            ->get('/admin/organizations')
            ->assertOk();
    }

    public function test_owner_ai_assistant_renders(): void
    {
        $owner = User::query()->where('email', 'owner@rentos.test')->firstOrFail();
        $org = $owner->organizations()->firstOrFail();

        $this->actingAs($owner)
            ->get('/owner/'.$org->slug.'/ai-assistant')
            ->assertOk()
            ->assertSee('Ask RentOS')
            ->assertSee('Suggested questions');
    }

    public function test_public_listing_detail_offers_apply_and_viewing(): void
    {
        $listing = Listing::query()->published()->firstOrFail();

        $this->get('/listings/'.$listing->slug)
            ->assertOk()
            ->assertSee('Book a viewing')
            ->assertSee('Apply');
    }

    public function test_tenant_can_open_discovery_and_home_screens(): void
    {
        $tenant = User::query()->where('email', 'tenant@rentos.test')->firstOrFail();

        $this->actingAs($tenant)->get('/tenant/viewings')->assertOk();
        $this->actingAs($tenant)->get('/tenant/notices')->assertOk();
        $this->actingAs($tenant)->get('/tenant/favorites')->assertOk();
        $this->actingAs($tenant)->get('/tenant/announcements')->assertOk();
        $this->actingAs($tenant)->get('/tenant/saved-searches')->assertOk();
        $this->actingAs($tenant)->get('/tenant/applications')->assertOk();
    }

    public function test_owner_can_open_new_operations_screens(): void
    {
        $owner = User::query()->where('email', 'owner@rentos.test')->firstOrFail();
        $org = $owner->organizations()->firstOrFail();
        $base = '/owner/'.$org->slug;

        $this->actingAs($owner)->get($base.'/viewings')->assertOk();
        $this->actingAs($owner)->get($base.'/notices')->assertOk();
        $this->actingAs($owner)->get($base.'/security-deposits')->assertOk();
        $this->actingAs($owner)->get($base.'/documents')->assertOk();
        $this->actingAs($owner)->get($base.'/inspections')->assertOk();
        $this->actingAs($owner)->get($base.'/conversations')->assertOk();
        $this->actingAs($owner)->get($base.'/payments')->assertOk();
        $this->actingAs($owner)->get($base.'/staff')->assertOk();
    }

    public function test_tenant_can_request_a_viewing_from_the_listing_page(): void
    {
        $tenant = User::query()->where('email', 'tenant@rentos.test')->firstOrFail();
        $listing = Listing::query()->published()->firstOrFail();

        $this->actingAs($tenant)
            ->post(route('listings.viewings.store', $listing), [
                'scheduled_at' => now()->addDay()->format('Y-m-d\TH:i'),
                'type' => 'in_person',
                'notes' => 'After 5pm',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('viewings', [
            'listing_id' => $listing->id,
            'applicant_id' => $tenant->id,
        ]);
    }

    public function test_favorites_api_lists_saved_homes(): void
    {
        $tenant = User::query()->where('email', 'tenant@rentos.test')->firstOrFail();
        $token = $tenant->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/favorites')
            ->assertOk()
            ->assertJsonStructure(['data']);
    }
}
