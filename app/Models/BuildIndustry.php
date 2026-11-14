<?php

namespace App\Models;

use App\Models\Concerns\HasActivityLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BuildIndustry extends Model
{
    use HasActivityLogs, SoftDeletes;

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
