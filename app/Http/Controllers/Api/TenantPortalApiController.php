<?php

namespace App\Http\Controllers\Api;

use App\Enums\MaintenanceStatus;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Lease;
use App\Models\MaintenanceRequest;
use App\Services\Billing\BillingService;
use Illuminate\Http\Request;

class TenantPortalApiController extends Controller
{
    public function leases(Request $request)
    {
        return $request->user()->leases()->with('property')->paginate(20);
    }

    public function invoices(Request $request)
    {
        return Invoice::query()->where('payer_id', $request->user()->id)->latest()->paginate(20);
    }

    public function pay(Request $request, Invoice $invoice, BillingService $billing)
    {
        abort_unless($invoice->payer_id === $request->user()->id, 403);

        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'method' => ['nullable', 'string'],
        ]);

        return $billing->recordPayment($invoice, $data['amount'], [
            'method' => $data['method'] ?? 'card',
            'gateway' => 'api',
        ]);
    }

    public function maintenance(Request $request)
    {
        return MaintenanceRequest::query()
            ->where('tenant_id', $request->user()->id)
            ->latest()
            ->paginate(20);
    }

    public function storeMaintenance(Request $request)
    {
        $data = $request->validate([
            'property_id' => ['required', 'exists:properties,id'],
            'lease_id' => ['nullable', 'exists:leases,id'],
            'title' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'priority' => ['nullable', 'string'],
            'category' => ['nullable', 'string'],
        ]);

        $lease = isset($data['lease_id'])
            ? Lease::query()->find($data['lease_id'])
            : $request->user()->leases()->where('property_id', $data['property_id'])->first();

        return MaintenanceRequest::query()->create([
            ...$data,
            'organization_id' => $lease?->organization_id,
            'tenant_id' => $request->user()->id,
            'status' => MaintenanceStatus::Submitted,
        ]);
    }
}
