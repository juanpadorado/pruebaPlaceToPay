<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    protected $table = 'products';

    protected $fillable = [
        'name', 'description', 'price'
    ];

    /*public function rooms()
    {
        return $this->hasMany('App\Room', 'cod_hotel', 'cod_hotel');
    }*/
}
