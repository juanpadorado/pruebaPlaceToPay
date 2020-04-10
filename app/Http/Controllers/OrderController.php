<?php

namespace App\Http\Controllers;

use App\Order;
use App\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use JWTAuth;

class OrderController extends Controller
{
    protected $user;

    public function __construct() {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    /*Funcion restonar todos los productos creados en el sistema*/
    public function index() {
        $products = Order::all();

        return response()->json([
            'success' => true,
            'orders' => $products
        ]);
    }

    public function store(Request $request) {
        try {
            $this->validate($request, [
                'products'     => 'required'
            ]);

            $products = $request->products;

            $order             = new Order();
            $order->user_id    = $this->user->id;
            $order->status     = 'CREATED';

            if ($order->save()) {

                foreach ($products as $value) {
                    $orderDetail = new OrderDetail();

                    $orderDetail->order_id = $order->id;
                    $orderDetail->product_id = $value['id'];

                    $orderDetail->save();
                }

                return response()->json([
                    'success' => true,
                    'message' => 'La orden se ha creado con éxito'
                ]);
            } else {
                throw new \InvalidArgumentException('La orden no se pudo crear');
            }
        }catch (\InvalidArgumentException $ex){
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage()
            ], 500);
        }catch (ValidationException $ex ) {
            return response()->json([
                'success' => false,
                'message' => $ex->errors()
            ], 400);
        }catch (\Exception $ex){
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage()
            ], 500);
        }
    }

    public function showDetail($id) {

        $data = DB::select(
            "SELECT prod.name, prod.price
               FROM order_detail orde,
                    products prod
              WHERE prod.id = orde.product_id
                AND orde.order_id = :order_id", ['order_id' => $id]);

        return response()->json([
            'success' => true,
            'products' => $data
        ]);
    }

}
