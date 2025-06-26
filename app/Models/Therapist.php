<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Therapist extends Model
{
    protected $fillable = ['name', 'no', 'gender'];
    protected static function booted()
    {
        static::creating(function ($therapist) {
            if (empty($therapist->therapist_id)) {
                $therapist->therapist_id = (string) str()->ulid();
            }
        });
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function todayAttendance()
    {
        return $this->hasOne(Attendance::class)
            ->whereDate('date', now()->toDateString());
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'tag_therapist');
    }

    /**
     * The services that the therapist has through their tags.
     *
     * This is the key change. We now define the hasManyThrough relationship
     * with all the foreign and local keys specified manually.
     */
    public function services()
    {
        return $this->hasManyThrough(
            Service::class,      // The final model we want to access (Service).
            TagTherapist::class, // The intermediate model we are going through (our new Pivot Model).
            'therapist_id',      // Foreign key on the intermediate table (tag_therapist.therapist_id).
            'tag_id',            // Foreign key on the final table (services.tag_id).
            'id',                // Local key on the starting table (therapists.id).
            'tag_id'             // Local key on the intermediate table (tag_therapist.tag_id).
        );
    }

}
