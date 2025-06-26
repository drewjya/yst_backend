<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// app/Models/HappyHourService.php
class HappyHourService extends Model
{
    protected $table = 'happy_hour_services';

    protected $fillable = ['happy_hour_id', 'service_id', 'promo_price'];

    public function happyHour()
    {
        return $this->belongsTo(HappyHour::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}

