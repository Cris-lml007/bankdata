<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    public $fillable = [
        'name'
    ];

    public function products(){
        return $this->belongsToMany(Product::class,'category_products','product_id','id');
    }
}
