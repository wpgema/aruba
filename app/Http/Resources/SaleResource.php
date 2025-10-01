<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'date' => $this->date?->toDateTimeString(),
            'user_id' => $this->user_id,
            'table_number' => $this->table_number,
            'total' => $this->total,
            'discount' => $this->discount,
            'grand_total' => $this->grand_total,
            'payment_method' => $this->payment_method,
            'paid_amount' => $this->paid_amount,
            'change_amount' => $this->change_amount,
            'status' => $this->status,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'sale_details' => SaleDetailResource::collection($this->whenLoaded('saleDetails')),
            'sale_details_count' => $this->when($this->relationLoaded('saleDetails'), function () {
                return $this->saleDetails->count();
            }),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}