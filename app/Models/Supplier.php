<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    /** @use HasFactory<\Database\Factories\SupplierFactory> */
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
        'address',
        'phone',
        'email',
        'city',
        'province',
        'postal_code',
    ];

    /**
     * Get the products for the supplier.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
