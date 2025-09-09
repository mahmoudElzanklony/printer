<?php

namespace App\Jobs;

use App\Models\orders;
use App\Services\External\ZohoBooksClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CreateZohoInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $orderId)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = orders::query()
            ->with(['user', 'payment'])
            ->find($this->orderId);

        $client  = new ZohoBooksClient();
        $invoice = $client->createInvoiceForOrder($order);

        if (! $invoice) {
            return;
        }

        try {
            $note = json_decode($order->note ?? '{}', true);
            if (! is_array($note)) {
                $note = [];
            }
            $note['zoho_invoice_id'] = $invoice['invoice_id'] ?? null;
            $note['zoho_invoice_number'] = $invoice['invoice_number'] ?? null;
            $order->update(['note' => json_encode($note, JSON_UNESCAPED_UNICODE)]);
        } catch (\Throwable $e) {
            Log::warning('Could not append Zoho invoice info into order note', ['order_id' => $this->orderId, 'err' => $e->getMessage()]);
        }
    }
}
