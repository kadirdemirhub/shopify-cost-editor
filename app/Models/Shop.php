<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Osiset\ShopifyApp\Contracts\ShopModel;
use Osiset\ShopifyApp\Traits\ShopModel as ShopifyShopModel;

class Shop extends Authenticatable implements ShopModel
{
    use ShopifyShopModel, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];
}