<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Overtrue\LaravelFavorite\Traits\Favoriteable;

class Merchant extends Authenticatable
{
    use HasFactory, Favoriteable, HasApiTokens;

    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'id',
        'created_at',
        'updated_at'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Scope to encode id as base64.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithBase64Id($query)
    {
        return $query->selectRaw('*, TO_BASE64(id) as encrypted_id');
    }

    /**
     * Accessor for the photo attribute.
     *
     * @return string
     */
    public function getPhotoAttribute($value)
    {
        return config('url.merchant_img') . $value;
    }

    public function product()
    {
        return $this->hasMany(Product::class, 'merchant_id', 'id');
    }
}
