<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Number;

class ChatProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $branchId = $request->input('branch_id');
        $flashSale = $this->activeFlashSale($branchId);
        $quantity = null;

        if ($flashSale) {
            $quantity = $flashSale->pivot->quantity - $flashSale->pivot->sale_quantity;

            if ($quantity == 0) {
                $quantity = null;
                $flashSale = null;
            }
        }

        $price = $this->price;
        $discountPrice = $flashSale ? $flashSale->pivot->price : $this->discount_price;
        return [
            'id' => $this->id,
            'name' =>  $this->name,
            'thumbnail' => $this->thumbnail,
            'discount_price' => (float) number_format($discountPrice, 2, '.', ''),
            'rating' => (float) $this->averageRating ?? 0.0,
            'total_reviews' => (string) Number::abbreviate($this->reviews?->count(), maxPrecision: 2),
            'price' => (float) number_format($price, 2, '.', ''),
        ];
    }
}
