<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;

class FrontController extends Controller
{
    public function index()
    {
        return Inertia::render('Index/Index');
    }

    public function submitRestaurantForm(Request $request)
    {
        $data = $request->all();

        return response()->json([
            'message' => 'Restaurant form submitted successfully!',
            'data' => $data,
        ]);
    }

    public function submitHotelForm(Request $request)
{
    $payload = $request->validate([
        'ticketFolio' => 'required',
        'checkIn' => 'required|date',

    ]);

    $response = Http::withHeaders([
        'Authorization' => 'Bearer ' . env('CLOUDBEDS_API_KEY'),
        'Accept'        => 'application/json',
    ])->get('https://api.cloudbeds.com/api/v1.3/getReservation', 
    ['reservationID' => $payload['ticketFolio']]);
        
    if ($response->successful()) {

        return response()->json([
            'message' => 'Enviado y recibido correctamente',
            'data'    => $response->json(),
        ]);
    }

    return response()->json([
        'message' => 'Error al llamar a la API',
        'status'  => $response->status(),
        'body'    => $response->json(),
    ], $response->status());

}};