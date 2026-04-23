<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    /**
     * Get standalone photos (not in any project) for this category.
     */
    public function standalonePhotos()
    {
        return $this->hasMany(Photo::class)->whereNull('project_id');
    }
}
