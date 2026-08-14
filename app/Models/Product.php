<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public $fillable = [
        'name',
        'cod',
        'price_purchase',
        'price_sale',
        'model'
    ];
}
