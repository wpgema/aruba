<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    /** @use HasFactory<\Database\Factories\SaleFactory> */
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'date',
        'user_id',
        'table_number',
        'total',
        'discount',
        'grand_total',
        'payment_method',
        'paid_amount',
        'change_amount',
        'status',
    ];

    protected $casts = [
        'date' => 'datetime',
        'total' => 'integer',
        'discount' => 'integer',
        'grand_total' => 'integer',
        'paid_amount' => 'integer',
        'change_amount' => 'integer',
    ];

    /**
     * Get the user that owns the sale.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sale details for the sale.
     */
    public function saleDetails(): HasMany
    {
        return $this->hasMany(SaleDetail::class);
    }

    /**
     * Get the products for the sale through sale details.
     */
    public function products()
    {
        return $this->hasManyThrough(
            Product::class,
            SaleDetail::class,
            'sale_id',
            'id',
            'id',
            'product_id'
        );
    }
}
