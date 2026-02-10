<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRestaurantCustomerPerKmRateToZonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('zones', function (Blueprint $table) {
            $table->double('Restaurant_perKm_Rate', 16, 3)->nullable()->after('per_km_shipping_charge');
            $table->double('Cust_Per_Km_Charge', 16, 3)->nullable()->after('Restaurant_perKm_Rate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('zones', function (Blueprint $table) {
            $table->dropColumn('Restaurant_perKm_Rate');
            $table->dropColumn('Cust_Per_Km_Charge');
        });
    }
}
