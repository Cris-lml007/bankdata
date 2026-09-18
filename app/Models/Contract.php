<?php

namespace App\Models;

use App\Enums\Status;
use App\Enums\TypePayment;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    public $fillable = [
        'customer_id',
        'status',
        'delivery_date',
        'destination',
        'method_payment',
        'priority',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function details()
    {
        return $this->hasMany(DetailContract::class);
    }

    public function assemblies()
    {
        return $this->hasMany(Assembly::class);
    }


    public function casts(): array
    {
        return [
            'status' => Status::class,
            'method_payment' => TypePayment::class,
        ];
    }
}
