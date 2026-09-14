<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus;
use App\Enums\AssistantPermission;
use App\Enums\DepositStatus;
use App\Enums\DocumentType;
use App\Enums\InspectionStatus;
use App\Enums\InspectionType;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\LeaseStatus;
use App\Enums\ListingStatus;
use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceStatus;
use App\Enums\NoticeStatus;
use App\Enums\NoticeType;
use App\Enums\OrganizationStatus;
use App\Enums\OrganizationType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Enums\ReferralStatus;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\ViewingStatus;
use App\Enums\ViewingType;
use App\Models\Amenity;
use App\Models\Announcement;
use App\Models\Application;
use App\Models\CalendarBlock;
use App\Models\Conversation;
use App\Models\Document;
use App\Models\Inspection;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Lease;
use App\Models\LeaseTemplate;
use App\Models\Listing;
use App\Models\MaintenanceRequest;
use App\Models\Notice;
use App\Models\Organization;
use App\Models\Plan;
use App\Models\Property;
use App\Models\Referral;
use App\Models\Review;
use App\Models\SavedSearch;
use App\Models\SecurityDeposit;
use App\Models\SupportTicket;
use App\Models\Task;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Viewing;
use App\Services\Buildings\BuildingGenerator;
use App\Support\Money;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RentosDemoSeeder extends Seeder
{
    public function run(): void
    {
        $plans = collect([
            ['Starter', 'starter', 0, 0, ['properties' => 3, 'staff' => 1]],
            ['Growth', 'growth', 490000, 4900000, ['properties' => 25, 'staff' => 10]],
            ['Portfolio', 'portfolio', 1490000, 14900000, ['properties' => 999, 'staff' => 100]],
        ])->map(fn ($plan) => Plan::query()->create([
            'name' => $plan[0],
            'description' => $plan[0].' SaaS plan',
            'price_monthly' => $plan[2],
            'price_yearly' => $plan[3],
            'currency' => 'BDT',
            'features' => ['listings', 'leases', 'payments', 'maintenance'],
            'limits' => $plan[4],
            'is_active' => true,
        ]));

        foreach (['Parking', 'Lift', 'Generator', 'Security', 'Balcony', 'Furnished kitchen'] as $name) {
            Amenity::query()->create(['name' => $name, 'category' => 'building']);
        }

        $admin = User::query()->create([
            'name' => 'Platform Admin',
            'email' => 'admin@rentos.test',
            'password' => Hash::make('password'),
            'role' => UserRole::SuperAdmin,
            'status' => UserStatus::Active,
            'email_verified_at' => now(),
        ]);

        $owner = User::query()->create([
            'name' => 'Nadia Rahman',
            'email' => 'owner@rentos.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Owner,
            'status' => UserStatus::Active,
            'phone' => '+8801711000001',
            'email_verified_at' => now(),
        ]);

        $staff = User::query()->create([
            'name' => 'Karim Ali',
            'email' => 'staff@rentos.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Assistant,
            'status' => UserStatus::Active,
            'email_verified_at' => now(),
        ]);

        $tenant = User::query()->create([
            'name' => 'Sara Ahmed',
            'email' => 'tenant@rentos.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Tenant,
            'status' => UserStatus::Active,
            'phone' => '+8801711000002',
            'email_verified_at' => now(),
        ]);

        $org = Organization::query()->create([
            'owner_user_id' => $owner->id,
            'plan_id' => $plans[1]->id,
            'name' => 'Rahman Homes',
            'type' => OrganizationType::Company,
            'status' => OrganizationStatus::Active,
            'email' => 'hello@rahmanhomes.test',
            'phone' => '+8802-123456',
            'country' => 'BD',
            'currency' => 'BDT',
            'timezone' => 'Asia/Dhaka',
        ]);

        $owner->forceFill(['current_organization_id' => $org->id])->save();
        $staff->forceFill(['current_organization_id' => $org->id])->save();

        $org->members()->attach($owner->id, [
            'role' => UserRole::Owner->value,
            'permissions' => AssistantPermission::defaultFor(UserRole::Owner),
            'status' => UserStatus::Active->value,
            'accepted_at' => now(),
        ]);

        $org->members()->attach($staff->id, [
            'role' => UserRole::Assistant->value,
            'permissions' => AssistantPermission::defaultFor(UserRole::Assistant),
            'status' => UserStatus::Active->value,
            'accepted_at' => now(),
        ]);

        $property = Property::query()->create([
            'organization_id' => $org->id,
            'owner_user_id' => $owner->id,
            'name' => 'Dhanmondi Lake Residences',
            'type' => PropertyType::Apartment,
            'status' => PropertyStatus::Occupied,
            'address_line' => 'House 12, Road 6',
            'city' => 'Dhaka',
            'state' => 'Dhaka',
            'postal_code' => '1205',
            'country' => 'BD',
            'bedrooms' => 2,
            'bathrooms' => 2,
            'square_feet' => 1100,
            'base_rent' => Money::fromMajor(35000),
            'security_deposit' => Money::fromMajor(70000),
            'currency' => 'BDT',
            'pet_friendly' => true,
            'furnished' => true,
            'amenities' => ['Parking', 'Lift', 'Security'],
            'description' => 'Bright 2-bed apartment overlooking Dhanmondi Lake, 5 minutes from Rabindra Sarobar.',
            'available_from' => now()->subMonths(2),
        ]);

        $vacant = Property::query()->create([
            'organization_id' => $org->id,
            'owner_user_id' => $owner->id,
            'name' => 'Gulshan Studio 4A',
            'type' => PropertyType::Studio,
            'status' => PropertyStatus::Vacant,
            'address_line' => 'Plot 8, Road 53',
            'city' => 'Dhaka',
            'state' => 'Dhaka',
            'postal_code' => '1212',
            'country' => 'BD',
            'bedrooms' => 1,
            'bathrooms' => 1,
            'square_feet' => 620,
            'base_rent' => Money::fromMajor(28000),
            'security_deposit' => Money::fromMajor(56000),
            'currency' => 'BDT',
            'furnished' => true,
            'description' => 'Compact Gulshan studio with dedicated desk nook and 24/7 security.',
            'available_from' => now()->addWeek(),
        ]);

        $generator = app(BuildingGenerator::class);

        $generator->generate($property, [
            'floors' => 4,
            'units_per_floor' => 3,
            'rooms' => 2,
            'baths' => 2,
            'balconies' => 1,
            'rent' => $property->base_rent,
            'deposit' => $property->security_deposit,
        ]);

        $generator->generate($vacant, [
            'floors' => 2,
            'units_per_floor' => 2,
            'rooms' => 1,
            'baths' => 1,
            'balconies' => 1,
            'rent' => $vacant->base_rent,
            'deposit' => $vacant->security_deposit,
        ]);

        $leasedListing = Listing::query()->create([
            'organization_id' => $org->id,
            'property_id' => $property->id,
            'title' => 'Lake-view 2-bed in Dhanmondi',
            'description' => $property->description,
            'status' => ListingStatus::Leased,
            'rent' => $property->base_rent,
            'security_deposit' => $property->security_deposit,
            'currency' => 'BDT',
            'available_from' => $property->available_from,
            'published_at' => now()->subMonths(3),
        ]);

        $openListing = Listing::query()->create([
            'organization_id' => $org->id,
            'property_id' => $vacant->id,
            'title' => 'Furnished Gulshan studio near Circle 2',
            'description' => $vacant->description,
            'status' => ListingStatus::Published,
            'rent' => $vacant->base_rent,
            'security_deposit' => $vacant->security_deposit,
            'currency' => 'BDT',
            'available_from' => $vacant->available_from,
            'is_featured' => true,
            'published_at' => now()->subDay(),
        ]);

        LeaseTemplate::query()->create([
            'organization_id' => $org->id,
            'name' => 'Bangladesh residential lease',
            'jurisdiction' => 'BD',
            'body' => 'Standard RentOS residential lease for Bangladesh.',
            'is_default' => true,
        ]);

        $application = Application::query()->create([
            'organization_id' => $org->id,
            'listing_id' => $leasedListing->id,
            'property_id' => $property->id,
            'applicant_id' => $tenant->id,
            'status' => ApplicationStatus::Approved,
            'monthly_income' => Money::fromMajor(120000),
            'employer' => 'North Star Digital',
            'employment_status' => 'full_time',
            'occupants' => 1,
            'consent_background_check' => true,
            'risk_score' => 82,
            'risk_notes' => 'Strong income-to-rent ratio. Documents complete.',
            'submitted_at' => now()->subMonths(3),
            'reviewed_at' => now()->subMonths(3)->addDay(),
            'reviewed_by' => $owner->id,
        ]);

        $lease = Lease::query()->create([
            'organization_id' => $org->id,
            'property_id' => $property->id,
            'application_id' => $application->id,
            'primary_tenant_id' => $tenant->id,
            'status' => LeaseStatus::Active,
            'starts_on' => now()->subMonths(2)->startOfMonth(),
            'ends_on' => now()->addMonths(10)->endOfMonth(),
            'rent' => $property->base_rent,
            'security_deposit' => $property->security_deposit,
            'currency' => 'BDT',
            'activated_at' => now()->subMonths(2),
            'terms' => 'Tenant pays rent by the 5th. Owner covers water. Tenant covers electricity.',
        ]);

        $lease->tenants()->attach($tenant->id, ['is_primary' => true, 'role' => 'tenant']);

        $occupiedUnit = $property->units()->where('code', '101')->first();

        if ($occupiedUnit) {
            $occupiedUnit->forceFill([
                'tenant_id' => $tenant->id,
                'status' => PropertyStatus::Occupied,
            ])->save();

            $lease->forceFill(['unit_id' => $occupiedUnit->id])->save();
            $property->forceFill(['status' => PropertyStatus::Occupied])->save();
        }

        $invoice = Invoice::query()->create([
            'organization_id' => $org->id,
            'lease_id' => $lease->id,
            'property_id' => $property->id,
            'payer_id' => $tenant->id,
            'type' => InvoiceType::Rent,
            'status' => InvoiceStatus::Open,
            'currency' => 'BDT',
            'subtotal' => $lease->rent,
            'total' => $lease->rent,
            'issue_date' => now()->startOfMonth(),
            'due_date' => now()->startOfMonth()->addDays(4),
        ]);

        InvoiceItem::query()->create([
            'invoice_id' => $invoice->id,
            'description' => 'Monthly rent',
            'quantity' => 1,
            'unit_amount' => $lease->rent,
            'amount' => $lease->rent,
        ]);

        Vendor::query()->create([
            'organization_id' => $org->id,
            'name' => 'QuickFix Plumbing',
            'category' => 'plumbing',
            'email' => 'jobs@quickfix.test',
            'phone' => '+8801711999888',
            'rating' => 4.6,
        ]);

        MaintenanceRequest::query()->create([
            'organization_id' => $org->id,
            'property_id' => $property->id,
            'lease_id' => $lease->id,
            'tenant_id' => $tenant->id,
            'assigned_to' => $staff->id,
            'title' => 'Kitchen tap dripping',
            'description' => 'Slow leak under the kitchen sink since Tuesday.',
            'category' => 'plumbing',
            'priority' => MaintenancePriority::Routine,
            'status' => MaintenanceStatus::Acknowledged,
        ]);

        Announcement::query()->create([
            'organization_id' => $org->id,
            'property_id' => $property->id,
            'author_id' => $staff->id,
            'title' => 'Water shutdown Friday 10–12',
            'body' => 'WASA maintenance. Please store water.',
            'published_at' => now(),
        ]);

        Task::query()->create([
            'organization_id' => $org->id,
            'property_id' => $property->id,
            'created_by' => $owner->id,
            'assigned_to' => $staff->id,
            'title' => 'Confirm showing for Gulshan studio',
            'due_on' => now()->addDays(2),
        ]);

        SupportTicket::query()->create([
            'organization_id' => $org->id,
            'user_id' => $owner->id,
            'subject' => 'Need SSLCommerz live keys',
            'body' => 'Please enable live checkout for Rahman Homes.',
            'priority' => 'normal',
        ]);

        SecurityDeposit::query()->create([
            'organization_id' => $org->id,
            'lease_id' => $lease->id,
            'amount' => $lease->security_deposit,
            'held_amount' => $lease->security_deposit,
            'currency' => 'BDT',
            'status' => DepositStatus::Held,
        ]);

        $tenant->favorites()->syncWithoutDetaching([$openListing->id]);

        SavedSearch::query()->create([
            'user_id' => $tenant->id,
            'name' => 'Gulshan studios',
            'criteria' => ['q' => 'Gulshan', 'min_bedrooms' => 1, 'pets' => false],
            'alert_frequency' => 'daily',
        ]);

        Viewing::query()->create([
            'organization_id' => $org->id,
            'listing_id' => $openListing->id,
            'property_id' => $vacant->id,
            'applicant_id' => $tenant->id,
            'host_id' => $staff->id,
            'type' => ViewingType::InPerson,
            'status' => ViewingStatus::Requested,
            'scheduled_at' => now()->addDays(2)->setTime(16, 0),
            'notes' => 'After work, around 4pm.',
        ]);

        Notice::query()->create([
            'organization_id' => $org->id,
            'lease_id' => $lease->id,
            'user_id' => $owner->id,
            'type' => NoticeType::Entry,
            'effective_on' => now()->addWeek(),
            'body' => 'AC servicing next week, 10–12.',
            'status' => NoticeStatus::Submitted,
        ]);

        $conversation = Conversation::query()->create([
            'organization_id' => $org->id,
            'property_id' => $property->id,
            'lease_id' => $lease->id,
            'subject' => 'Kitchen leak follow-up',
            'last_message_at' => now(),
        ]);
        $conversation->participants()->sync([$owner->id, $tenant->id, $staff->id]);
        $conversation->messages()->create([
            'user_id' => $tenant->id,
            'body' => 'The plumber visited but the drip is back.',
            'channel' => 'in_app',
        ]);

        Document::query()->create([
            'organization_id' => $org->id,
            'uploaded_by' => $tenant->id,
            'documentable_type' => Lease::class,
            'documentable_id' => $lease->id,
            'title' => 'National ID copy',
            'type' => DocumentType::Id,
            'path' => 'documents/demo-nid.pdf',
        ]);

        Inspection::query()->create([
            'organization_id' => $org->id,
            'property_id' => $property->id,
            'unit_id' => $occupiedUnit?->id,
            'lease_id' => $lease->id,
            'inspector_id' => $staff->id,
            'type' => InspectionType::MoveIn,
            'status' => InspectionStatus::Completed,
            'scheduled_at' => now()->subMonths(2),
            'completed_at' => now()->subMonths(2),
            'checklist' => [
                ['item' => 'Kitchen sink', 'condition' => 'good'],
                ['item' => 'AC', 'condition' => 'fair', 'notes' => 'Needs service'],
            ],
        ]);

        CalendarBlock::query()->create([
            'organization_id' => $org->id,
            'property_id' => $vacant->id,
            'starts_on' => now()->addDays(10),
            'ends_on' => now()->addDays(12),
            'reason' => 'Owner occupancy',
            'source' => 'manual',
        ]);

        Review::query()->create([
            'organization_id' => $org->id,
            'reviewer_id' => $tenant->id,
            'reviewee_type' => Property::class,
            'reviewee_id' => $property->id,
            'rating' => 5,
            'body' => 'Great location and responsive staff.',
            'is_revealed' => false,
        ]);

        Referral::query()->create([
            'referrer_id' => $tenant->id,
            'code' => 'SARA2026',
            'credit_amount' => 50000,
            'status' => ReferralStatus::Pending,
        ]);

        $admin->forceFill(['current_organization_id' => $org->id])->save();
    }
}
