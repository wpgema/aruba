<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'price',
        'subtotal',
        'note',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'integer',
        'subtotal' => 'integer',
    ];

    /**
     * Get the sale that owns the sale detail.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the product that owns the sale detail.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}