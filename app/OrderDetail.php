<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{

    protected $table = 'order_detail';

    protected $fillable = [
        'order_id', 'product_id'
    ];

    /*public function rooms()
    {
        return $this->hasMany('App\Room', 'cod_hotel', 'cod_hotel');
    }*/
}
