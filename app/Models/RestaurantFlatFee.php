<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantFlatFee extends Model
{
    use HasFactory;

    protected $table = 'restaurant_flat_fee';

    protected $guarded = ['id'];

    public $timestamps = true;

    protected $casts = [
        'flat_fee_from' => 'float',
        'flat_fee_to' => 'float',
        'flat_fee' => 'float',
        'base_payout' => 'float',
    ];

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
    
    // RestaurantFlatFee model
    public static function latestByZone()
    {
        return self::latest()->first();
    }

}
