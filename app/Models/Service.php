<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['name', 'description', "tag_id", "is_active", "duration"];

    protected static function booted()
    {
        static::creating(function ($service) {
            if (empty($service->service_id)) {
                $service->service_id = (string) str()->ulid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'service_id'; // IMPORTANT: for route model binding
    }

    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }

    public function therapists() {
        return $this->hasManyThrough(
            Therapist::class,      // The final model we want to access (Service).
            TagTherapist::class, // The intermediate model we are going through (our new Pivot Model).
            'therapist_id',      // Foreign key on the intermediate table (tag_therapist.therapist_id).
            'tag_id',            // Foreign key on the final table (services.tag_id).
            'id',                // Local key on the starting table (therapists.id).
            'tag_id'             // Local key on the intermediate table (tag_therapist.tag_id).
        );
    }

    public function category()
    {
        return $this->hasOneThrough(
            Category::class,
            Tag::class,
            'id',         // Foreign key on Tag table (Tag's primary key is 'id')
            'id',         // Foreign key on Category table (Category's primary key is 'id')
            'tag_id',     // Local key on Service table (Service belongs to Tag via 'tag_id')
            'category_id' // Local key on Tag table (Tag belongs to Category via 'category_id')
        );
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_service')
            ->withPivot('price')
            ->withTimestamps();
    }

    public function happyHours()
    {
        return $this->hasMany(HappyHour::class);
    }

}
