<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;

class Project extends Model
{
    protected $fillable = [
        'title', 'slug', 'description', 'cover_image', 'category_id',
        'instagram_url', 'date', 'location', 'is_featured', 'status',
        'delivery_enabled', 'delivery_token', 'delivery_password', 'delivery_title',
        'delivery_message', 'delivery_download_count', 'delivery_expires_at'
    ];

    protected $casts = [
        'date' => 'date',
        'is_featured' => 'boolean',
        'delivery_enabled' => 'boolean',
        'delivery_expires_at' => 'datetime',
        'delivery_download_count' => 'integer',
    ];

    protected $appends = ['delivery_url'];

    public function getDeliveryUrlAttribute(): ?string
    {
        return $this->delivery_token ? url('/livraison/' . $this->delivery_token) : null;
    }

    public function generateDeliveryToken(bool $save = true): string
    {
        $this->delivery_token = Str::random(40);
        if ($save) {
            $this->save();
        }
        return $this->delivery_token;
    }

    public static function generateSecurePassword(int $length = 10): string
    {
        // Génère un mot de passe robuste et lisible (sans caractères visuellement ambigus)
        $letters = '23456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        $specials = '!@#$%&*';
        $password = '';
        
        for ($i = 0; $i < $length - 2; $i++) {
            $password .= $letters[random_int(0, strlen($letters) - 1)];
        }
        // Ajouter au moins un chiffre et un caractère spécial
        $password .= $specials[random_int(0, strlen($specials) - 1)];
        $password .= random_int(2, 9);
        
        return str_shuffle($password);
    }

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
