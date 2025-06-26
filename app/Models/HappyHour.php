<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HappyHour extends Model
{

    protected $fillable = ['days', 'start_time', 'end_time', 'branch_id'];
    protected $casts    = [
        'days' => 'array',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function branchServicePromos()
    {
        return $this->belongsToMany(BranchService::class, 'happy_hour_services')
            ->withPivot('promo_price')
            ->withTimestamps();
    }

}
