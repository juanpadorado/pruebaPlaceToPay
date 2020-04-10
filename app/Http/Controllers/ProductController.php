<?php

namespace App\Http\Controllers;

use App\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
/*use JWTAuth;*/

class ProductController extends Controller
{
    protected $user;

    /*public function __construct() {
        $this->user = JWTAuth::parseToken()->authenticate();
    }*/

    /*Funcion restonar todos los productos creados en el sistema*/
    public function index(){
        $products = Product::all();

        return response()->json([
            'success' => true,
            'products' => $products
        ]);
    }

}
