<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Services\Documents\InvoicePdfService;
use Database\Seeders\RentosDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class InvoicePdfTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RentosDemoSeeder::class);
    }

    public function test_invoice_pdf_is_returned_as_a_streamed_download(): void
    {
        $invoice = Invoice::query()->firstOrFail();
        $response = app(InvoicePdfService::class)->download($invoice);

        $this->assertInstanceOf(StreamedResponse::class, $response);

        ob_start();
        $response->sendContent();
        $content = (string) ob_get_clean();

        $this->assertStringStartsWith('%PDF', $content);
    }
}
