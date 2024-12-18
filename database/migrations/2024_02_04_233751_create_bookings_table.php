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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('app_users')->onDelete('cascade');
            $table->unsignedBigInteger('service_id');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            // $table->unsignedBigInteger('from_city_id');
            // $table->unsignedBigInteger('to_city_id');
            // $table->foreign('from_city_id')->references('id')->on('cities')->onDelete('cascade');
            // $table->foreign('to_city_id')->references('id')->on('cities')->onDelete('cascade');
            $table->string("from_lat")->nullable();
            $table->string("from_long")->nullable();
            $table->string("to_lat")->nullable();
            $table->string("to_long")->nullable();
            $table->date('date');
            $table->time('time');
            $table->enum('status',['in_stage','accepted','prepared','executed','canceld'])->default('received');
            $table->text('note')->nullable();
            $table->tinyInteger('paid')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
