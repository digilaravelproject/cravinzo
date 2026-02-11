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
        Schema::create('restaurant_flat_fee', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zone_id');
            $table->decimal('flat_fee_from', 8, 2)->nullable();
            $table->decimal('flat_fee_to', 8, 2)->nullable();
            $table->decimal('flat_fee', 8, 2)->nullable();
            $table->decimal('petrol_price', 8, 2)->nullable();
            $table->timestamps();

            $table->foreign('zone_id')
                  ->references('id')
                  ->on('zones')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_flat_fee');
    }
};
