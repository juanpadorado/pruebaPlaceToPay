<?php

namespace App\Http\Controllers;

use App\Order;
use App\OrderDetail;
use Dnetix\Redirection\PlacetoPay;
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

        if ($this->user->profile === env('PROFILECUSTOMER')) {
            $products = Order::where('user_id', $this->user->id)
                ->orderBy('id', 'desc')
                ->get();
        } else if ($this->user->profile === env('PROFILEADMIN')) {
            $products = Order::orderBy('id', 'desc')->get();
        }

        $placeToPay = $this->placeToPay();

        foreach ($products as $value) {
            $statusOld = $value->status;

            if ($statusOld === 'CREATED') {
                $response = $placeToPay->query($value->request_id);

                if ($response->status()->status() === 'REJECTED') {
                    $value->status = 'REJECTED';
                } else if ($response->status()->status() === 'APPROVED') {
                    $value->status = 'PAYED';
                } else if ($response->status()->status() === 'PENDING') {
                    $value->status = 'CREATED';
                }

                if ($statusOld != $value->status) {
                    $value->save();
                }
            }

        }

        return response()->json([
            'success' => true,
            'orders' => $products
        ]);
    }

    public function store(Request $request) {
        DB::beginTransaction();
        try {
            $this->validate($request, [
                'products'     => 'required'
            ]);

            $total = 0;
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
                    $total = $total + $value['price'];
                }

                $placeToPay = $this->placeToPay();

                $reference = 'ORDEN_' . $order->id;
                $requestSend = $requestSend = $this->infoPay($reference, $total);

                $response = $placeToPay->request($requestSend);

                if ($response->isSuccessful()) {
                    // STORE THE $response->requestId() and $response->processUrl() on your DB associated with the payment order
                    // Redirect the client to the processUrl or display it on the JS extension
                    $order->request_id = $response->requestId();
                    $order->process_url = $response->processUrl();

                    $order->save();

                    DB::commit();
                    return response()->json([
                        'success' => true,
                        'message' => 'La orden se ha creado con éxito y será redireccionado a la pasarela de pago.',
                        'processUrl' =>  $order->process_url
                    ]);
                } else {
                    // Mensaje de error
                    throw new \InvalidArgumentException($response->status()->message());
                }
            } else {
                throw new \InvalidArgumentException('La orden no se pudo crear');
            }
        }catch (\InvalidArgumentException $ex){
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage()
            ], 500);
        }catch (ValidationException $ex ) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $ex->errors()
            ], 400);
        }catch (\Exception $ex){
            DB::rollBack();
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

    public function retryPayment(Request $request) {
        DB::beginTransaction();
        try {
            $this->validate($request, [
                'idOrder'     => 'required'
            ]);

            $total = 0;
            $order = Order::find($request->idOrder);

            foreach ($order->orderDetails as $value){
                $total = $total + $value->product->price;
            }

            $order->status     = 'CREATED';

            $placeToPay = $this->placeToPay();

            $reference = 'ORDEN_' . $order->id;
            $requestSend = $this->infoPay($reference, $total);

            $response = $placeToPay->request($requestSend);

            if ($response->isSuccessful()) {
                // STORE THE $response->requestId() and $response->processUrl() on your DB associated with the payment order
                // Redirect the client to the processUrl or display it on the JS extension
                $order->request_id = $response->requestId();
                $order->process_url = $response->processUrl();

                $order->save();

                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'El pago de la orden se intentara nuevamente y será redireccionado a la pasarela de pago.',
                    'processUrl' =>  $order->process_url
                ]);
            } else {
                // Mensaje de error
                throw new \InvalidArgumentException($response->status()->message());
            }

        }catch (\InvalidArgumentException $ex){
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage()
            ], 500);
        }catch (ValidationException $ex ) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $ex->errors()
            ], 400);
        }catch (\Exception $ex){
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage()
            ], 500);
        }
    }

    public function infoPay($reference, $total) {
        return [
            "locale" => "es_CO",
            "buyer" => [
                "name" => $this->user->name,
                "email" => $this->user->email,
                "mobile" => $this->user->phone,
            ],
            'payment' => [
                'reference' => $reference,
                'description' => 'Compra en My store',
                'amount' => [
                    'currency' => 'COP',
                    'total' => $total,
                ],
            ],
            'expiration' => date('c', strtotime('+7 minutes')),
            'returnUrl' => 'http://localhost:4200/#/listOrder',
            'ipAddress' => '127.0.0.1',
            'userAgent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36',
        ];
    }
}
