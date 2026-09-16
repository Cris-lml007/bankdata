<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    public $fillable = [
        'valid_from',
        'valid_to',
        'delivery_date',
        'user_id'
    ];


    public function details()
    {
        return $this->hasMany(DetailQuotation::class);
    }

    public function contract()
    {
        return $this->hasOne(Contract::class);
    }
}
