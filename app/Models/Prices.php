<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Prices extends Model
{
    public function product()
    {
        return $this->belongsTo(Products::class);
    }

    public function store()
    {
        return $this->belongsTo(Stores::class);
    }
}
