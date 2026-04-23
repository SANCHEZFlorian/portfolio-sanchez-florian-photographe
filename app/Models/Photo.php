<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    protected $fillable = [
        'project_id', 'category_id', 'image_path', 'thumbnail_path',
        'caption', 'instagram_tag', 'span_cols', 'span_rows',
        'display_order', 'is_highlighted', 'is_cover'
    ];

    protected $casts = [
        'is_highlighted' => 'boolean',
        'is_cover' => 'boolean',
        'span_cols' => 'integer',
        'span_rows' => 'integer',
        'display_order' => 'integer',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * A photo is either in a project OR standalone with a category, never both.
     */
    public function scopeStandalone($query)
    {
        return $query->whereNull('project_id');
    }

    public function scopeInProject($query)
    {
        return $query->whereNotNull('project_id');
    }
}
