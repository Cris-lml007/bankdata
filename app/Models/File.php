<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    public $fillable = [
        'name',
        'mime',
        'path',
        'product_id',
    ];
}
