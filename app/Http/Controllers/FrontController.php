<?php

namespace App\Http\Controllers;

use App\Http\Requests\HotelFormRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\CloudbedsService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class FrontController extends Controller
{
    protected CloudbedsService $cloudbedsService;
    
    public function __construct(CloudbedsService $cloudbedsService)
    {
        $this->cloudbedsService = $cloudbedsService;
    }

    public function index()
    {   // Página de inicio
    return Inertia::render('Index/Index');
    }

    public function submitRestaurantForm(Request $request)
    {   // Manejar el envío del formulario del restaurante
        $data = $request->all();

        return response()->json([
            'message' => 'Restaurant form submitted successfully!',
            'data' => $data,
        ]);
    }

    
    public function submitHotelForm(HotelFormRequest $request)
    {
        $data = $request->validated();
        try{
            return $this->cloudbedsService->getReservationData($data['ticketFolio'], $data['checkOut']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener datos de la reserva',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    public function showBillingForm(Request $request, $reservationID)
    {
        // Parsear el checkout desde query param
        $providedCheckOut = null;
        if ($request->filled('checkOut')) {
            try {
                $providedCheckOut = Carbon::parse($request->query('checkOut'))->toDateString();
            } catch (\Throwable $e) {
                return redirect()->route('home')->with('error', 'Fecha de check-out inválida');
            }
        }

        $response = $this->cloudbedsService->fetchReservationFromAPI($reservationID);

        $data = $response->json();
        $reservation = $data['data'] ?? $data;

        $validation = $this->cloudbedsService->validateReservationData($reservation, $providedCheckOut);

        if (!$validation['valid']) {
            return redirect()->route('home')->with('error', $validation['error']);
        }

        return Inertia::render('Billing/BillingForm', [
            'reservation' => $reservation,
        ]);
    }
}