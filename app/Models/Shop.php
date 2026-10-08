<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    protected $fillable = [
        'shopify_domain',
        'access_token',
        'scopes',
        'associated_user',
        'installed_at',
    ];

    protected $hidden = [
        'access_token',
    ];

    protected $casts = [
        'installed_at' => 'datetime',
    ];

    public function quotes()
    {
        return $this->hasMany(Quote::class, 'shop_domain', 'shopify_domain');
    }
}