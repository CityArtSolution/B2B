<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get all of the orders for the Payment
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_payments', 'payment_id', 'order_id');
    }

    /**
     * Mark a payment verified with its gateway, and its orders, as paid, exactly once.
     * The browser redirect and the gateway's server notification can arrive together, so the rows are locked.
     * Each order is saved as a model so the Order updated event takes its stock.
     */
    public function markPaid(): void
    {
        DB::transaction(function () {
            $locked = self::whereKey($this->id)->lockForUpdate()->first();
            if (! $locked || $locked->is_paid) {
                return;
            }

            foreach ($locked->orders()->lockForUpdate()->get() as $order) {
                if ($order->payment_status !== PaymentStatus::PAID) {
                    $order->update(['payment_status' => PaymentStatus::PAID->value]);
                }
            }

            $locked->update(['is_paid' => true]);
        });

        $this->refresh();
    }
}
