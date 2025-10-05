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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->dateTime('date');
            $table->foreign('user_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->string('table_number')->nullable();
            $table->integer('total');
            $table->integer('discount')->default(0);
            $table->integer('grand_total');
            $table->enum('payment_method', ['cash', 'card', 'qris'])->default('cash');
            $table->integer('paid_amount')->default(0);
            $table->integer('change_amount')->default(0);
            $table->enum('status', ['paid', 'unpaid', 'cancelled'])->default('paid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
