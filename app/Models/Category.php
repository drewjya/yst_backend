<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{

    protected $fillable = ['name', 'description'];

    protected static function booted()
    {
        static::creating(function ($category) {
            if (empty($category->category_id)) {
                $category->category_id = (string) str()->ulid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'category_id'; // IMPORTANT: for route model binding
    }
    
    public function tags()
    {
        return $this->hasMany(Tag::class);
    }

}
