<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    protected $table = 'orders';

    protected $fillable = [
        'status', 'user_id'
    ];

    /*public function rooms()
    {
        return $this->hasMany('App\Room', 'cod_hotel', 'cod_hotel');
    }*/
}
