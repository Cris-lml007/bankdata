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
        'model',
        'description',
        'type_factory_id',
    ];

    public function typeFactory()
    {
        return $this->belongsTo(TypeFactory::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class,'category_products','product_id','category_id');
    }

    public function assemblies()
    {
        return $this->hasMany(Assembly::class);
    }


    public function componentProducts()
    {
        return $this->hasMany(ComponentProduct::class);
    }

    public function tagProducts()
    {
        return $this->hasMany(TagProduct::class);
    }

    public function files()
    {
        return $this->hasMany(File::class);
    }
}
