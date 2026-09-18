<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComponentProduct extends Model
{
    public $fillable = [
        'product_id',
        'name',
        'description',
        'quantity',
        'type_factory_id',
    ];

    public function typeFactory(){
        return $this->belongsTo(TypeFactory::class);
    }

    public function tagProducts(){
        return $this->hasmany(TagProduct::class,'product_id','product_id');
    }

    public function assemblyComponents()
    {
        return $this->hasMany(
            AssemblyComponent::class
        );
    }

}
