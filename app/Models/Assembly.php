<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Model;

class Assembly extends Model
{
    protected $fillable = [
        'product_id',
        'contract_id',
        'quantity',
        'industry_id',
        'status',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function industry()
    {
        return $this->belongsTo(Industry::class);
    }

    public function components()
    {
        return $this->hasMany(AssemblyComponent::class);
    }

    public function builds()
    {
        return $this->morphMany(
            BuildIndustry::class,
            'buildeable'
        );
    }

    public function casts(): array
    {
        return [
            'status' => Status::class,
        ];
    }
}
