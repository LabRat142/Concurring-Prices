<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public function prices()
    {
        return $this->hasMany(Price::class);
    }
}
