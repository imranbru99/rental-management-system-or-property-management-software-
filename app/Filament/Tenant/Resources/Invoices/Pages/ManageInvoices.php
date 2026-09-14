<?php

namespace App\Filament\Tenant\Resources\Invoices\Pages;

use App\Filament\Tenant\Resources\Invoices\InvoiceResource;
use Filament\Resources\Pages\ManageRecords;

class ManageInvoices extends ManageRecords
{
    protected static string $resource = InvoiceResource::class;
}
