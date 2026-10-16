<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    public $fillable = [
        'name',
        'description',
        'industry_id',
        'to',
    ];
    protected function casts(): array
    {
        return [
            'to' => 'date',
        ];
    }

    public function industry()
    {
        return $this->belongsTo(\App\Models\Industry::class);
    }
}
