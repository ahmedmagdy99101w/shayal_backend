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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('discount_code');
            $table->string('type');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('discount')->nullable();
            $table->decimal('discount_percentage')->nullable();
            $table->integer('max_usage')->nullable();
            $table->decimal('max_discount_value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
