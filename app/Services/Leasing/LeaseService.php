<?php

namespace App\Services\Leasing;

use App\Enums\ApplicationStatus;
use App\Enums\DepositStatus;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\LeaseStatus;
use App\Enums\ListingStatus;
use App\Enums\PropertyStatus;
use App\Models\Application;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Lease;
use App\Models\SecurityDeposit;
use App\Services\Billing\BillingService;
use App\Services\Documents\LeasePdfService;

class LeaseService
{
    public function __construct(
        protected BillingService $billing,
        protected LeasePdfService $pdf,
    ) {}

    public function createFromApplication(Application $application, array $overrides = []): Lease
    {
        $listing = $application->listing;

        $lease = Lease::query()->create(array_merge([
            'organization_id' => $application->organization_id,
            'property_id' => $application->property_id,
            'unit_id' => $application->unit_id,
            'application_id' => $application->id,
            'primary_tenant_id' => $application->applicant_id,
            'status' => LeaseStatus::Draft,
            'starts_on' => $listing?->available_from ?? now()->toDateString(),
            'ends_on' => now()->addYear()->toDateString(),
            'rent' => $listing?->rent ?? 0,
            'security_deposit' => $listing?->security_deposit ?? 0,
            'currency' => $listing?->currency ?? 'BDT',
        ], $overrides));

        $lease->tenants()->sync([
            $application->applicant_id => ['is_primary' => true, 'role' => 'tenant'],
        ]);

        $application->forceFill([
            'status' => ApplicationStatus::Approved,
            'reviewed_at' => now(),
        ])->save();

        $this->pdf->store($lease);

        return $lease->refresh();
    }

    public function activate(Lease $lease): Lease
    {
        $lease->forceFill([
            'status' => LeaseStatus::Active,
            'activated_at' => now(),
        ])->save();

        $lease->property?->forceFill(['status' => PropertyStatus::Occupied])->save();
        $lease->application?->listing?->forceFill(['status' => ListingStatus::Leased])->save();

        if ($lease->security_deposit > 0) {
            SecurityDeposit::query()->create([
                'organization_id' => $lease->organization_id,
                'lease_id' => $lease->id,
                'amount' => $lease->security_deposit,
                'held_amount' => $lease->security_deposit,
                'currency' => $lease->currency,
                'status' => DepositStatus::Held,
            ]);

            $depositInvoice = Invoice::query()->create([
                'organization_id' => $lease->organization_id,
                'lease_id' => $lease->id,
                'property_id' => $lease->property_id,
                'payer_id' => $lease->primary_tenant_id,
                'type' => InvoiceType::Deposit,
                'status' => InvoiceStatus::Open,
                'currency' => $lease->currency,
                'subtotal' => $lease->security_deposit,
                'tax' => 0,
                'total' => $lease->security_deposit,
                'amount_paid' => 0,
                'issue_date' => now()->toDateString(),
                'due_date' => $lease->starts_on,
            ]);

            InvoiceItem::query()->create([
                'invoice_id' => $depositInvoice->id,
                'description' => 'Security deposit',
                'quantity' => 1,
                'unit_amount' => $lease->security_deposit,
                'amount' => $lease->security_deposit,
            ]);
        }

        $this->billing->createRentInvoice($lease, $lease->starts_on);

        return $lease->refresh();
    }
}
