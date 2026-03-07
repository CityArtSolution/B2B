<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;

class ProductResource extends JsonResource
{
    private function getTranslationsMap(string $field, mixed $fallback = null): array
    {
        $translations = $this->relationLoaded('translations')
            ? $this->translations
            : $this->translations()->get();

        $values = $translations
            ->filter(fn ($translation) => filled($translation->lang) && filled($translation->{$field} ?? null))
            ->mapWithKeys(fn ($translation) => [$translation->lang => $translation->{$field}])
            ->toArray();

        if (filled($fallback) && ! array_key_exists('en', $values)) {
            $values['en'] = $fallback;
        }

        return $values;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->load(['reviews', 'orders', 'quantities' , 'sizes', 'colors', 'unit', 'brand', 'shop', 'flashSales', 'translations']);

        $lang = request()->header('accept-language') ?? 'en';

        $favorite = false;
        $user = Auth::guard('api')->user();

        if ($user && $user->customer) {
            $favorite = $user->customer->favorites()->where('product_id', $this->id)->exists();
        }

        $discountPercentage = $this->getDiscountPercentage($this->price, $this->discount_price);
        $totalSold = $this->orders->sum('pivot.quantity');

        $flashSale = $this->flashSales?->first();
        $flashSaleProduct = null;
        $quantity = null;

        if ($flashSale) {
            $flashSaleProduct = $flashSale?->products()->where('id', $this->id)->first();

            $quantity = $flashSaleProduct?->pivot->quantity - $flashSaleProduct->pivot->sale_quantity;

            if ($quantity == 0) {
                $quantity = null;
                $flashSaleProduct = null;
            } else {
                $discountPercentage = $flashSale?->pivot->discount;
            }
        }

        $price = $this->price;
        $carton_price=$this->carton_price;
        $discountPrice = $flashSaleProduct ? $flashSaleProduct->pivot->price : $this->discount_price;

        $translation = $this->translations->where('lang', $lang)->first();
        $name = $translation?->name ?? $this->name;
        $description = $translation?->description ?? $this->description;
        $shortDescription = $translation?->short_description ?? $this->short_description;

        $brandTranslation = $this->brand?->translations()?->where('lang', $lang)->first();
        $brandName = $brandTranslation?->name ?? $this->brand?->name;

        return [
            'id' => $this->id,
            'is_digital' => (bool) $this->is_digital,
            'name' => $name,
            'short_description' => $shortDescription,
            'description' => $description,
            'translations' => [
                'name' => $this->getTranslationsMap('name', $this->name),
                'short_description' => $this->getTranslationsMap('short_description', $this->short_description),
                'description' => $this->getTranslationsMap('description', $this->description),
            ],
            'code' => $this->code,
            'thumbnail' => $this->thumbnail,
            'branch_qty' => $this->productBranches->map(function($branch) {
                return [
                    'branch_id' => $branch->branch_id,
                    'qty' => $branch->qty
                ];
            }),

            'price' => (float) number_format($price, 2, '.', ''),
            'discount_price' => (float) number_format($discountPrice, 2, '.', ''),
            'discount_percentage' => (float) number_format($discountPercentage, 2, '.', ''),
            'carton_price'=>(float) number_format($carton_price, 2, '.', ''),
            'carton_units_count'=>(int) $this->carton_units_count,
            'rating' => (float) $this->averageRating ?? 0.0,
            'total_reviews' => (string) Number::abbreviate($this->reviews?->count(), maxPrecision: 2),
            'total_sold' => (string) number_format($totalSold, 0, '.', ','),
            'quantities' => $this->productBranches->map(fn($branch) => [
                'branch_id' => $branch->branch_id,
                'branch_name' => $branch->branch->name ?? null,
                'qty' => $branch->qty
            ]),
            'is_favorite' => (bool) $favorite,
            'sizes' => SizeResource::collection($this->sizes),
            'colors' => ColorResource::collection($this->colors),
            'unit' => $this->unit ? UnitResource::make($this->unit) : null,
            'brand' => $brandName,
            'shop' => ProductShopResource::make($this->shop),
        ];
    }
}
