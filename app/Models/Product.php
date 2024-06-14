<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Overtrue\LaravelFavorite\Traits\Favoriteable;

class Product extends Model
{
    use HasFactory, Favoriteable;

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
     * Scope to encode id as base64.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithBase64CategoryId($query)
    {
        return $query->selectRaw('TO_BASE64(category_id) as encrypted_category_id');
    }

    /**
     * Scope to encode id as base64.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithBase64MerchantId($query)
    {
        return $query->selectRaw('TO_BASE64(merchant_id) as encrypted_merchant_id');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'id',
        'category_id',
        'merchant_id',
    ];

    /**
     * Accessor for the photo attribute.
     *
     * @return string
     */
    public function getImageAttribute($value)
    {
        return config('url.product_img') . $value;
    }

    /**
     * Accessor for the price attribute.
     *
     * @return string
     */
    public function getPriceAttribute($value)
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id', 'id');
    }
}
