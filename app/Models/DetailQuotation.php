<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailQuotation extends Model
{
    public $fillable = [
        'product_id',
        'quantity',
        'price',
        'quotation_id',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
