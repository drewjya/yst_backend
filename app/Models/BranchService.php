<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchService extends Model
{
    protected $table = 'branch_service';

    protected $fillable = ['branch_id', 'service_id', 'price'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function happyHours()
    {
        return $this->belongsToMany(HappyHour::class, 'happy_hour_services')
            ->withPivot('promo_price')
            ->withTimestamps();
    }

}
