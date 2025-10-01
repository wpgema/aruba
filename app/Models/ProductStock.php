<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStock extends Model
{
    /** @use HasFactory<\Database\Factories\ProductStockFactory> */
    use HasFactory;

    protected $fillable = [
        'product_id',
        'date',
        'stock',
    ];

    /**
     * Get the product that owns the product stock.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
