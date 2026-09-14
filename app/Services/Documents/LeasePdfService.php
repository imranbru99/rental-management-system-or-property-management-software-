<?php

namespace App\Services\Documents;

use App\Models\Lease;
use ImranDev\UnicodePdf\Facades\UnicodePdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeasePdfService
{
    public function download(Lease $lease): StreamedResponse
    {
        $lease->loadMissing(['property', 'primaryTenant', 'organization']);

        $binary = UnicodePdf::preset($this->preset($lease->organization?->country))
            ->loadView('pdf.lease', ['lease' => $lease])
            ->a4()
            ->output();

        return $this->stream($binary, 'lease-'.$lease->number.'.pdf');
    }

    public function store(Lease $lease): string
    {
        $lease->loadMissing(['property', 'primaryTenant', 'organization']);
        $path = 'leases/'.$lease->number.'.pdf';

        UnicodePdf::preset($this->preset($lease->organization?->country))
            ->loadView('pdf.lease', ['lease' => $lease])
            ->a4()
            ->store($path, 'local');

        $lease->forceFill(['pdf_path' => $path])->save();

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
