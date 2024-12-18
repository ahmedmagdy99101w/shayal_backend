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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('app_user_id');  
            $table->decimal('credit', 24, 2)->nullable();  
            $table->foreign('app_user_id')->references('id')->on('app_users')->onDelete('cascade');
            $table->decimal('debit', 24, 2)->nullable();  
            $table->decimal('balance', 24, 2);  
            $table->enum('transaction_type', ['add', 'deduction']);  
            $table->unsignedBigInteger('offer_submitted_id')->nullable();  
            $table->foreign('offer_submitted_id')->references('id')->on('offer_submitteds')->onDelete('cascade');
            $table->unsignedBigInteger('provider_id')->nullable();  
            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('cascade');
            $table->timestamps();  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
