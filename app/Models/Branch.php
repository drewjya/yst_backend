<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{

    protected $fillable = ['name', 'address', 'phone_number', 'is_active', 'open_hour', 'close_hour'];
    public function admins()
    {
        return $this->hasMany(User::class);
    }

    protected static function booted()
    {
        static::creating(function ($branch) {
            if (empty($branch->branch_id)) {
                $branch->branch_id = (string) str()->ulid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'branch_id'; // IMPORTANT: for route model binding
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'branch_service')
            ->withPivot('price')
            ->withTimestamps();
    }

    public function happyHours()
    {
        return $this->hasMany(HappyHour::class);
    }

    public function branchServices()
    {
        return $this->hasMany(BranchService::class);
    }

    public function therapists()  {
        return $this->hasMany(Therapist::class);
    }

}
