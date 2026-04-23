<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'title', 'slug', 'description', 'cover_image', 'category_id',
        'instagram_url', 'date', 'location', 'is_featured', 'status'
    ];

    protected $casts = [
        'date' => 'date',
        'is_featured' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function photos()
    {
        return $this->hasMany(Photo::class)->orderBy('display_order');
    }

    public function coverPhoto()
    {
        return $this->hasOne(Photo::class)->where('is_cover', true);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
