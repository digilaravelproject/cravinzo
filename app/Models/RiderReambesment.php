<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiderReambesment extends Model
{
    use HasFactory;

    protected $table = 'rider_reimbursement';

    protected $fillable = [
        'rider_id',
        'distance',
    ];

    protected $casts = [
        'rider_id' => 'integer',
        'distance' => 'float',
    ];

    public function rider()
    {
        return $this->belongsTo(DeliveryMan::class, 'rider_id', 'id');
    }
}
