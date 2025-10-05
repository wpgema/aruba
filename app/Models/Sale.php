<?php

namespace App\Models;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
     * Get the employee that owns the sale.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'user_id');
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
