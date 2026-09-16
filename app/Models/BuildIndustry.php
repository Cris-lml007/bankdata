<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuildIndustry extends Model
{
    protected $fillable = [
        'industry_id',
        'quantity',
        'buildeable_type',
        'buildeable_id',
    ];

    public function industry()
    {
        return $this->belongsTo(Industry::class);
    }

    public function buildeable()
    {
        return $this->morphTo();
    }
}
