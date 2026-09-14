<?php

namespace App\Services\Billing;

use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Lease;
use App\Models\LedgerEntry;
use App\Models\Payment;
use App\Services\Documents\InvoicePdfService;
use Illuminate\Support\Carbon;

class BillingService
{
    public function __construct(protected InvoicePdfService $pdf) {}

    public function createRentInvoice(Lease $lease, ?\DateTimeInterface $dueDate = null): Invoice
    {
        $due = $dueDate ? Carbon::parse($dueDate) : now()->startOfMonth()->addDays($lease->due_day - 1);

        $invoice = Invoice::query()->create([
            'organization_id' => $lease->organization_id,
            'lease_id' => $lease->id,
            'property_id' => $lease->property_id,
            'payer_id' => $lease->primary_tenant_id,
            'type' => InvoiceType::Rent,
            'status' => InvoiceStatus::Open,
            'currency' => $lease->currency,
            'subtotal' => $lease->rent,
            'tax' => 0,
            'total' => $lease->rent,
            'amount_paid' => 0,
            'issue_date' => now()->toDateString(),
            'due_date' => $due->toDateString(),
        ]);

        InvoiceItem::query()->create([
            'invoice_id' => $invoice->id,
            'description' => 'Monthly rent — '.$lease->property?->name,
            'quantity' => 1,
            'unit_amount' => $lease->rent,
            'amount' => $lease->rent,
        ]);

        $this->ledger($invoice, 'rent_receivable', 'debit', $invoice->total, 'Rent invoice '.$invoice->number);
        $this->pdf->store($invoice);

        return $invoice->refresh();
    }

    public function recordPayment(Invoice $invoice, int $amount, array $attributes = []): Payment
    {
        $payment = Payment::query()->create(array_merge([
            'organization_id' => $invoice->organization_id,
            'invoice_id' => $invoice->id,
            'payer_id' => $invoice->payer_id,
            'amount' => $amount,
            'currency' => $invoice->currency,
            'status' => PaymentStatus::Completed,
            'paid_at' => now(),
        ], $attributes));

        $invoice->amount_paid += $amount;
        $invoice->status = $invoice->amount_paid >= $invoice->total
            ? InvoiceStatus::Paid
            : InvoiceStatus::Partial;
        if ($invoice->status === InvoiceStatus::Paid) {
            $invoice->paid_at = now();
        }
        $invoice->save();

        $this->ledger($invoice, 'cash', 'debit', $amount, 'Payment '.$payment->reference);
        $this->ledger($invoice, 'rent_receivable', 'credit', $amount, 'Apply payment '.$payment->reference);

        return $payment;
    }

    public function applyLateFees(): int
    {
        $count = 0;

        Invoice::query()
            ->where('type', InvoiceType::Rent)
            ->where('status', InvoiceStatus::Open)
            ->whereDate('due_date', '<', now()->toDateString())
            ->with('lease')
            ->each(function (Invoice $invoice) use (&$count): void {
                $lease = $invoice->lease;
                if (! $lease) {
                    return;
                }

                $grace = now()->subDays($lease->late_fee_grace_days);
                if ($invoice->due_date->greaterThan($grace)) {
                    return;
                }

                $exists = Invoice::query()
                    ->where('lease_id', $lease->id)
                    ->where('type', InvoiceType::LateFee)
                    ->whereDate('issue_date', now()->toDateString())
                    ->exists();

                if ($exists) {
                    return;
                }

                $fee = (int) round($invoice->total * ($lease->late_fee_percent / 100));
                if ($fee <= 0) {
                    return;
                }

                $late = Invoice::query()->create([
                    'organization_id' => $invoice->organization_id,
                    'lease_id' => $lease->id,
                    'property_id' => $invoice->property_id,
                    'payer_id' => $invoice->payer_id,
                    'type' => InvoiceType::LateFee,
                    'status' => InvoiceStatus::Open,
                    'currency' => $invoice->currency,
                    'subtotal' => $fee,
                    'tax' => 0,
                    'total' => $fee,
                    'amount_paid' => 0,
                    'issue_date' => now()->toDateString(),
                    'due_date' => now()->toDateString(),
                    'notes' => 'Late fee for '.$invoice->number,
                ]);

                InvoiceItem::query()->create([
                    'invoice_id' => $late->id,
                    'description' => 'Late fee',
                    'quantity' => 1,
                    'unit_amount' => $fee,
                    'amount' => $fee,
                ]);

                $invoice->status = InvoiceStatus::Overdue;
                $invoice->save();
                $count++;
            });

        return $count;
    }

    protected function ledger(Invoice $invoice, string $account, string $type, int $amount, string $memo): void
    {
        LedgerEntry::query()->create([
            'organization_id' => $invoice->organization_id,
            'property_id' => $invoice->property_id,
            'lease_id' => $invoice->lease_id,
            'reference_type' => Invoice::class,
            'reference_id' => $invoice->id,
            'account' => $account,
            'entry_type' => $type,
            'amount' => $amount,
            'currency' => $invoice->currency,
            'memo' => $memo,
            'posted_at' => now(),
        ]);
    }
}
