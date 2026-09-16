<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Industry extends Model
{
    protected $fillable = [
        'name',
        'type_factory_id',
        'address',
        'phone',
    ];

    public function typeFactory()
    {
        return $this->belongsTo(TypeFactory::class);
    }

    public function assemblies()
    {
        return $this->hasMany(Assembly::class);
    }

    public function assemblyComponents()
    {
        return $this->hasMany(AssemblyComponent::class);
    }

    public function builds()
    {
        return $this->hasMany(BuildIndustry::class);
    }

    public function users() {
        return $this->hasMany(User::class);
    }
}
