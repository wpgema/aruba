<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'price_buy',
        'price_sale',
        'category_id',
        'supplier_id',
        'description',
        'stock',
        'image',
        'is_active',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the supplier that owns the product.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the product stocks for the product.
     */
    public function productStocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }

    /**
     * Get the sale details for the product.
     */
    public function saleDetails(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }

    /**
     * Get the sales for the product through sale details.
     */
    public function sales()
    {
        return $this->hasManyThrough(
            Sale::class,
            SaleDetail::class,
            'product_id',
            'id',
            'id',
            'sale_id'
        );
    }
}
