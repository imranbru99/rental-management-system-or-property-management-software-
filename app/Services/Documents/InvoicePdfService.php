<?php

namespace App\Services\Documents;

use App\Models\Invoice;
use ImranDev\UnicodePdf\Facades\UnicodePdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoicePdfService
{
    public function download(Invoice $invoice): StreamedResponse
    {
        $invoice->loadMissing(['items', 'payer', 'property', 'organization']);

        $binary = UnicodePdf::preset($this->preset($invoice->organization?->country))
            ->loadView('pdf.invoice', ['invoice' => $invoice])
            ->a4()
            ->output();

        return $this->stream($binary, $invoice->number.'.pdf');
    }

    public function store(Invoice $invoice): string
    {
        $invoice->loadMissing(['items', 'payer', 'property', 'organization']);
        $path = 'invoices/'.$invoice->number.'.pdf';

        UnicodePdf::preset($this->preset($invoice->organization?->country))
            ->loadView('pdf.invoice', ['invoice' => $invoice])
            ->a4()
            ->store($path, 'local');

        $invoice->forceFill(['pdf_path' => $path])->save();

        return $path;
    }

    protected function stream(string $binary, string $filename): StreamedResponse
    {
        return response()->streamDownload(static function () use ($binary): void {
            echo $binary;
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    protected function preset(?string $country): string
    {
        return match ($country) {
            'BD' => 'bengali',
            'AE' => 'arabic',
            'IN' => 'hindi',
            default => 'latin',
        };
    }
}
