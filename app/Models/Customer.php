<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    public $fillable = [
        'name',
        'email',
        'phone',
        'ci_nit',
        'organization'
    ];
}
