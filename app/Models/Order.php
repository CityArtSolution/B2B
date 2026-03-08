<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\OrderStatus2;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Scopes\PosOrderFalse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'order_status' => OrderStatus::class,
        'order_status2' => OrderStatus2::class,
        'payment_method' => PaymentMethod::class,
        'payment_status' => PaymentStatus::class,
    ];

    /**
     * Get all of the products for the Order.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'order_products')->withPivot('quantity', 'branch_id', 'color', 'unit', 'size', 'price')->withoutGlobalScopes();
    }

    /**
     * Get all of the vat taxes for the Order.
     */
    public function vatTaxes(): HasMany
    {
        return $this->hasMany(OrderVatTax::class, 'order_id');
    }

    /**
     * Get the customer that owns the Order.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get the shop for the Order.
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    /**
     * Get the coupon for the Order.
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupon_id')->withTrashed();
    }

    /**
     * Get the address for the Order.
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    /**
     * Get the payments for the Order.
     */
    public function payments(): BelongsToMany
    {
        return $this->belongsToMany(Payment::class, 'order_payments');
    }

    /**
     * Get the driver order for the Order.
     */
    public function driverOrder(): BelongsTo
    {
        return $this->belongsTo(DriverOrder::class, 'id', 'order_id');
    }

    /**
     * apply global scope
     */
    protected static function booted()
    {
        static::addGlobalScope(new PosOrderFalse);
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function () {
            self::clearOrderCache();
        });

        static::updated(function () {
            self::clearOrderCache();
        });

        static::deleted(function () {
            self::clearOrderCache();
        });
    }

    protected static function clearOrderCache()
    {
        $cacheKeys = [
            'admin_all_orders',
            'shop_all_orders',
        ];

        foreach (OrderStatus::cases() as $status) {
            $cacheKeys[] = 'admin_status_'.Str::camel($status->value);
            $cacheKeys[] = 'shop_status_'.Str::camel($status->value);
        }

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    public function getInvoiceTotalAttribute(): float
    {
        $subtotal = $this->products_subtotal > 0 ? $this->products_subtotal : (float) ($this->total_amount ?? 0);
        $delivery = (float) ($this->delivery_charge ?? 0);
        $tax = (float) ($this->tax_amount ?? 0);
        $discount = (float) ($this->coupon_discount ?? 0);

        return round(($subtotal + $delivery + $tax) - $discount, 2);
    }

    public function getProductsSubtotalAttribute(): float
    {
        if ($this->relationLoaded('products')) {
            $subtotal = $this->products->sum(function ($product) {
                return ((float) ($product->pivot->price ?? 0)) * ((int) ($product->pivot->quantity ?? 0));
            });

            return round((float) $subtotal, 2);
        }

        $subtotal = $this->products()
            ->selectRaw('COALESCE(SUM(order_products.price * order_products.quantity), 0) as subtotal')
            ->value('subtotal');

        return round((float) $subtotal, 2);
    }
}
