<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    public function prices()
    {
        return $this->hasMany(Price::class);
    }
}
