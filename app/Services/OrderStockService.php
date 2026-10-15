<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\FlashSale;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

/**
 * Keeps branch stock, flash-sale sold counts and the shop's remaining sales in step with an order.
 *
 * Orders paid through a gateway in DEFERRED_METHODS take nothing at checkout. Their stock is taken
 * when the order becomes Paid (see the Order model's updated event), so a failed or abandoned
 * payment leaves stock untouched. Other orders keep the existing rules: stock is taken at checkout,
 * or when an Offer Price order is accepted.
 */
class OrderStockService
{
    public const DEFERRED_METHODS = [PaymentMethod::TAP];

    /**
     * Whether this payment method waits for a confirmed payment before taking stock.
     * Accepts the enum, its value ("Tap") or its name in any case ("tap").
     */
    public static function defersStock(PaymentMethod|string|null $method): bool
    {
        if (is_string($method)) {
            $method = PaymentMethod::tryFrom($method)
                ?? collect(PaymentMethod::cases())->first(fn ($case) => strcasecmp($case->name, $method) === 0);
        }

        return in_array($method, self::DEFERRED_METHODS, true);
    }

    /**
     * Whether stock is currently taken for the order.
     * Pass the status the order had before a change when judging a status update.
     */
    public static function isTaken(Order $order, ?OrderStatus $status = null): bool
    {
        $status ??= $order->order_status;

        if ($status === OrderStatus::CANCELLED) {
            return false;
        }
        if ($order->payment_method === PaymentMethod::OFFER_PRICE) {
            return $status !== OrderStatus::PENDING;
        }
        if (self::defersStock($order->payment_method)) {
            return $order->payment_status === PaymentStatus::PAID;
        }

        return true;
    }

    /**
     * Take stock for a deferred order once its payment is confirmed.
     */
    public static function take(Order $order): void
    {
        foreach ($order->products as $product) {
            $qty = (int) $product->pivot->quantity;

            $branchProduct = $product->quantityBranch($product->pivot->branch_id);
            if ($branchProduct) {
                $branchProduct->decrement('qty', $qty);

                if ($branchProduct->qty < 0) {
                    Log::warning('Branch stock went below zero after a paid order', [
                        'order_id' => $order->id, 'product_id' => $product->id,
                        'branch_id' => $product->pivot->branch_id, 'qty' => $branchProduct->qty,
                    ]);
                }
            }

            $flashSale = self::flashSaleFor($product);
            if ($flashSale) {
                $flashSale->products()->updateExistingPivot($product->id, [
                    'sale_quantity' => $flashSale->pivot->sale_quantity + $qty,
                ]);
            }
        }

        if (generaleSetting('setting')?->business_based_on == 'subscription') {
            $subscription = $order->shop?->currentSubscription;

            if ($subscription && $subscription->remaining_sales > 0) {
                $subscription->update(['remaining_sales' => $subscription->remaining_sales - 1]);
            }
        }
    }

    /**
     * Give back the stock an order took, when it is cancelled.
     */
    public static function release(Order $order): void
    {
        foreach ($order->products as $product) {
            $qty = (int) $product->pivot->quantity;

            $product->quantityBranch($product->pivot->branch_id)?->increment('qty', $qty);

            $flashSale = self::flashSaleFor($product);
            if ($flashSale && $flashSale->pivot->sale_quantity >= $qty) {
                $flashSale->products()->updateExistingPivot($product->id, [
                    'sale_quantity' => $flashSale->pivot->sale_quantity - $qty,
                ]);
            }
        }
    }

    /**
     * The active flash sale an order line was priced from, or null when it was sold at the normal price.
     * The line price is the flash price plus any size and color surcharge, as set at checkout.
     */
    private static function flashSaleFor($product): ?FlashSale
    {
        $flashSale = $product->activeFlashSale($product->pivot->branch_id);
        if (! $flashSale) {
            return null;
        }

        $surcharge = 0.0;
        if (filled($product->pivot->size)) {
            $surcharge += (float) ($product->sizes()->where('name', $product->pivot->size)->first()?->pivot?->price ?? 0);
        }
        if (filled($product->pivot->color)) {
            $surcharge += (float) ($product->colors()->where('name', $product->pivot->color)->first()?->pivot?->price ?? 0);
        }

        $flashLinePrice = (float) $flashSale->pivot->price + $surcharge;

        return abs((float) $product->pivot->price - $flashLinePrice) < 0.01 ? $flashSale : null;
    }
}
