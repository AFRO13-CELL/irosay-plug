<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PurchaseService
{
    public function __construct(private InventoryService $inventory)
    {
    }

    /**
     * Recalculate and persist a purchase's total from its current line items.
     * Called after every item add/remove so the header total never drifts
     * out of sync with the lines.
     */
    public function recalculateTotal(Purchase $purchase): void
    {
        $total = $purchase->items()->get()->sum(fn (PurchaseItem $item) => $item->quantity * $item->buying_price);
        $purchase->update(['total_amount' => $total]);
    }

    /**
     * Confirm receipt of a purchase. Only allowed once every serialized
     * line has had its full quantity of individual IMEI devices captured
     * (each of which already posted its own stock_movement when it was
     * added). Quantity-tracked lines post their stock increase here, in a
     * single transaction, exactly once — a purchase can never be received
     * twice.
     */
    public function receive(Purchase $purchase, User $user): void
    {
        if ($purchase->status !== 'pending') {
            throw new RuntimeException('This purchase has already been received or cancelled.');
        }

        if ($purchase->items->isEmpty()) {
            throw new RuntimeException('Add at least one item before receiving this purchase.');
        }

        $purchase->load('items.product');

        if (! $purchase->readyToReceive()) {
            throw new RuntimeException('Every serialized line (e.g. iPhones) needs its IMEI devices captured before this purchase can be received.');
        }

        DB::transaction(function () use ($purchase, $user) {
            foreach ($purchase->items as $item) {
                if ($item->product->tracking_type === 'quantity') {
                    $this->inventory->adjustStock(
                        $item->product,
                        $item->quantity,
                        $user,
                        type: 'purchase',
                        reference: $purchase->reference,
                        notes: "Received via Purchase {$purchase->reference}",
                    );
                }
                // Serialized items already posted their movements one-by-one
                // as each device was captured — nothing further to do here.
            }

            $purchase->update([
                'status' => 'received',
                'received_at' => now(),
            ]);

            AuditLog::record($user, 'purchase.received', $purchase, "Purchase {$purchase->reference} marked received");
        });
    }
}
