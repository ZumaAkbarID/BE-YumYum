<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

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
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'id'
    ];

    /**
     * Accessor for the photo attribute.
     *
     * @return string
     */
    public function getImageAttribute($value)
    {
        return config('url.category_img') . $value;
    }

    public function product()
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }
}
