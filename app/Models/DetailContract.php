<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailContract extends Model
{
    protected $fillable = [
        'contract_id',
        'product_id',
        'quantity',
        'price',
    ];

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
