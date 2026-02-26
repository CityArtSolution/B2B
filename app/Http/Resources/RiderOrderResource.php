<?php

namespace App\Http\Resources;

use App\Enums\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RiderOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->order->id,
            'order_code' => (string) '#'.$this->order->prefix.''.$this->order->order_code,
            'amount' => (float) number_format($this->order->payable_amount, 2, '.', ''),
            'order_status' => $this->order->order_status->value,
            'payment_status' => $this->order->payment_status->value,
            'payment_method' => match ($this->order->payment_method->value) {
                PaymentMethod::NEW_CLIENT      => 'New_Client',
                PaymentMethod::PREVIOUS_CLIENT => 'Previous_Client',
                default                        => 'Online',
            },
            'user' => [
                'name' => $this->order->customer->user->name,
                'phone' => $this->order->customer->user->phone,
                'address' => AddressResource::make($this->order->address),
            ],
            'shop' => [
                'name' => $this->order->shop->name,
                'phone' => $this->order->shop->user->phone,
                'address' => $this->order->shop->address,
                'latitude' => $this->order->shop->latitude,
                'longitude' => $this->order->shop->longitude,
            ],
        ];
    }
}
