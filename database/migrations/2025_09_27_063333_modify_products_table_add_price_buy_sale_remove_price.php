<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Menambahkan field price_buy (nullable) dan price_sale (required)
            $table->integer('price_buy')->nullable()->after('name');
            $table->integer('price_sale')->default(0)->after('price_buy');
            
            // Menghapus field price lama
            $table->dropColumn('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Mengembalikan field price
            $table->integer('price')->default(0)->after('name');
            
            // Menghapus field price_buy dan price_sale
            $table->dropColumn(['price_buy', 'price_sale']);
        });
    }
};
