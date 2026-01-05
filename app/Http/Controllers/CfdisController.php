<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class CfdisController extends Controller
{
    public function generateInvoice(Request $request)
{
    $username = env('FACTURAMA_USERAGENT');
    $password = env('FACTURAMA_PASSWORD');
    
    $datosCfdi = $request->input('cfdiData');

    $response = Http::withBasicAuth($username, $password)
        ->withoutVerifying()
        ->post('https://apisandbox.facturama.mx/3/cfdis', $datosCfdi);

    return response()->json([
        'status' => $response->status(),
        'body' => $response->json()
    ], $response->status());
}
}
