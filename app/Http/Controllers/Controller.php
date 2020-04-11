<?php

namespace App\Http\Controllers;

use Dnetix\Redirection\PlacetoPay;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function placeToPay() {
        return new PlacetoPay([
            'login' => env('LOGIN'),
            'tranKey' => env('TRANKEY'),
            'type' => PlacetoPay::TP_REST,
            'url' => env('URL')
        ]);
    }
}
