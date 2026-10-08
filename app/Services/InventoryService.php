<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\PhoneDevice;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class InventoryService
{
    /**
     * Change a quantity-tracked product's stock and record the movement.
     * This is the ONLY place product.stock_quantity should ever be written —
     * never update it directly from a controller. $delta is signed:
     * positive to receive stock, negative to remove it.
     */
    public function adjustStock(
        Product $product,
        int $delta,
        User $user,
        string $type = 'adjustment',
        ?string $reference = null,
        ?string $notes = null,
    ): StockMovement {
        if ($product->tracking_type !== 'quantity') {
            throw new InvalidArgumentException(
                'Serialized products (iPhones) are stocked one IMEI unit at a time — use changeDeviceStatus() instead.'
            );
        }

        return DB::transaction(function () use ($product, $delta, $user, $type, $reference, $notes) {
            $locked = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();

            $previous = $locked->stock_quantity;
            $new = $previous + $delta;

            if ($new < 0) {
                throw new RuntimeException("Adjustment would take \"{$locked->name}\" stock below zero (currently {$previous}, change {$delta}).");
            }

            $locked->stock_quantity = $new;
            $locked->save();

            $movement = StockMovement::create([
                'product_id' => $locked->id,
                'phone_device_id' => null,
                'type' => $type,
                'quantity' => $delta,
                'previous_stock' => $previous,
                'new_stock' => $new,
                'user_id' => $user->id,
                'reference' => $reference,
                'notes' => $notes,
            ]);

            AuditLog::record(
                $user,
                'stock.adjusted',
                $locked,
                "{$locked->name}: {$previous} -> {$new} ({$type}" . ($reference ? ", {$reference}" : '') . ')'
            );

            return $movement;
        });
    }

    /**
     * Register a brand-new IMEI-tracked unit (a receiving/creation event).
     * A serialized product's "stock" is never a stored number — it's always
     * derived by counting phone_devices rows — so this writes a
     * stock_movement for visibility but never touches products.stock_quantity.
     */
    public function receiveDevice(PhoneDevice $device, User $user, ?string $reference = null, ?string $notes = null): StockMovement
    {
        return DB::transaction(function () use ($device, $user, $reference, $notes) {
            $before = PhoneDevice::where('product_id', $device->product_id)->where('status', 'in_stock')->count() - 1; // device already saved as in_stock before this call
            $after = $before + 1;

            $movement = StockMovement::create([
                'product_id' => $device->product_id,
                'phone_device_id' => $device->id,
                'type' => 'purchase',
                'quantity' => 1,
                'previous_stock' => max($before, 0),
                'new_stock' => $after,
                'user_id' => $user->id,
                'reference' => $reference,
                'notes' => $notes,
            ]);

            AuditLog::record($user, 'phone_device.received', $device, "Received IMEI {$device->imei1}" . ($reference ? " ({$reference})" : ''));

            return $movement;
        });
    }

    /**
     * Change a device's status (in_stock <-> reserved, etc.) and record the
     * movement in one transaction. $delta is the signed change to the
     * product's "available" (in_stock) count that this status change causes
     * — e.g. reserving a unit is -1, cancelling a reservation is +1.
     */
    public function changeDeviceStatus(
        PhoneDevice $device,
        string $newStatus,
        string $movementType,
        int $delta,
        User $user,
        ?string $reference = null,
        ?string $notes = null,
    ): StockMovement {
        return DB::transaction(function () use ($device, $newStatus, $movementType, $delta, $user, $reference, $notes) {
            $locked = PhoneDevice::whereKey($device->id)->lockForUpdate()->firstOrFail();

            $before = PhoneDevice::where('product_id', $locked->product_id)->where('status', 'in_stock')->count();

            $oldStatus = $locked->status;
            $locked->status = $newStatus;
            $locked->save();

            $after = PhoneDevice::where('product_id', $locked->product_id)->where('status', 'in_stock')->count();

            $movement = StockMovement::create([
                'product_id' => $locked->product_id,
                'phone_device_id' => $locked->id,
                'type' => $movementType,
                'quantity' => $delta,
                'previous_stock' => $before,
                'new_stock' => $after,
                'user_id' => $user->id,
                'reference' => $reference,
                'notes' => $notes,
            ]);

            AuditLog::record(
                $user,
                'phone_device.' . $movementType,
                $locked,
                "IMEI {$locked->imei1}: {$oldStatus} -> {$newStatus}" . ($reference ? " ({$reference})" : '')
            );

            return $movement;
        });
    }
}
