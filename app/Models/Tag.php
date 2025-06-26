<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = ['name', 'category_id'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function services(){
        return $this->hasMany(Service::class);
    }

    public function therapists()
    {
        return $this->belongsToMany(Therapist::class, 'tag_therapist');
    }

    

}
