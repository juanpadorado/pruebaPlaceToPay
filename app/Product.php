<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    protected $table = 'products';

    protected $fillable = [
        'name', 'description', 'price'
    ];

    public function orderDetails()
    {
        return $this->hasMany('App\OrderDetail', 'product_id', 'id');
    }
}
