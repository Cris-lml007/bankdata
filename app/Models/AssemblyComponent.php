<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssemblyComponent extends Model
{
    protected $fillable = [
        'assembly_id',
        'component_id',
        'quantity',
        'industry_id',
        'status',
    ];

    public function assembly()
    {
        return $this->belongsTo(Assembly::class);
    }

    public function component()
    {
        return $this->belongsTo(ComponentProduct::class);
    }

    public function industry()
    {
        return $this->belongsTo(Industry::class);
    }

    public function builds()
    {
        return $this->morphMany(
            BuildIndustry::class,
            'buildeable'
        );
    }
}
