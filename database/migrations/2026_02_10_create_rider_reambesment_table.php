<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRiderReambesmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rider_reambesment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rider_id');
            $table->double('distance', 10, 2)->nullable();
            $table->timestamps();
            
            $table->foreign('rider_id')->references('id')->on('delivery_men')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rider_reambesment');
    }
}
