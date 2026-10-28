<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Cart;
use App\Models\FlashSale;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

/**
 * Keeps branch stock, flash-sale sold counts, the shop's remaining sales and the customer's cart
 * in step with an order.
 *
 * Orders paid online (Tap, Tabby, Tamara, PayPal, ...) change nothing at checkout. When the order
 * becomes Paid (see the Order model's updated event) its stock is taken and its cart lines are
 * removed, so a failed or abandoned payment leaves stock and cart untouched.
 * Offline orders keep the existing rules: stock is taken and the cart emptied at checkout,
 * or for Offer Price, stock is taken when the order is accepted.
 */
class OrderStockService
{
    /** Methods confirmed without an online payment */
    public const OFFLINE_METHODS = [PaymentMethod::NEW_CLIENT, PaymentMethod::PREVIOUS_CLIENT, PaymentMethod::OFFER_PRICE];

    /**
     * Whether this is an online payment method, which waits for a confirmed payment before
     * taking stock and emptying the cart.
     * Accepts the enum, its value ("Tap") or its name in any case ("tap", "Previous_client").
     * An unknown method counts as offline, which keeps the old checkout behaviour.
     */
    public static function defersStock(PaymentMethod|string|null $method): bool
    {
        if (is_string($method)) {
            $method = PaymentMethod::tryFrom($method)
                ?? collect(PaymentMethod::cases())->first(fn ($case) => strcasecmp($case->name, $method) === 0);
        }

        return $method !== null && ! in_array($method, self::OFFLINE_METHODS, true);
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
     * Remove a paid online order's lines from the customer's cart.
     * Checkout orders a whole shop's cart, so the order's products in that shop and branch are its lines.
     */
    public static function clearCart(Order $order): void
    {
        $lines = $order->products->map(fn ($product) => [$product->id, $product->pivot->branch_id]);
        if ($lines->isEmpty() || ! $order->customer_id) {
            return;
        }

        Cart::query()
            ->where('customer_id', $order->customer_id)
            ->where('shop_id', $order->shop_id)
            ->where(function ($query) use ($lines) {
                foreach ($lines as [$productId, $branchId]) {
                    $query->orWhere(function ($line) use ($productId, $branchId) {
                        $line->where('product_id', $productId);
                        $branchId === null ? $line->whereNull('branch_id') : $line->where('branch_id', $branchId);
                    });
                }
            })
            ->delete();
    }

    /**
     * Cancel the customer's earlier online orders for these shops that were never paid.
     * Each failed or abandoned online payment left a Pending order behind, so retrying the
     * payment piled up duplicate orders. Placing a new order from the same cart replaces them.
     * They took no stock (online orders take it when paid), so nothing has to be given back;
     * a payment that still arrives for one of them is logged by the Order model.
     */
    public static function cancelAbandonedAttempts(int $customerId, array $shopIds): int
    {
        $onlineMethods = collect(PaymentMethod::cases())
            ->reject(fn ($method) => in_array($method, self::OFFLINE_METHODS, true))
            ->map(fn ($method) => $method->value)
            ->all();

        $orders = Order::withoutGlobalScopes()
            ->where('customer_id', $customerId)
            ->whereIn('shop_id', $shopIds)
            ->whereIn('payment_method', $onlineMethods)
            ->where('payment_status', PaymentStatus::PENDING->value)
            ->where('order_status', OrderStatus::PENDING->value)
            ->get();

        foreach ($orders as $order) {
            $order->update(['order_status' => OrderStatus::CANCELLED->value]);
        }

        return $orders->count();
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
