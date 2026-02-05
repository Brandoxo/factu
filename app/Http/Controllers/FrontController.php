<?php

namespace App\Http\Controllers;

use App\Http\Requests\HotelFormRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\CloudbedsService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;
use App\Services\FacturamaFilesService;
use Illuminate\Support\Facades\Log;

class FrontController extends Controller
{
    protected CloudbedsService $cloudbedsService;
    protected FacturamaFilesService $facturamaFilesService;
    
    public function __construct(CloudbedsService $cloudbedsService, FacturamaFilesService $facturamaFilesService)
    {
        $this->cloudbedsService = $cloudbedsService;
        $this->facturamaFilesService = $facturamaFilesService;
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
            'filteredRoomsAvailable' => array_values($this->cloudbedsService->extractAllRooms($reservation)),
        ]);
    }

    public function billingSuccess()
    {
        $billingData = session('billing_success_data');

        return Inertia::render('Billing/BillingSuccess', [
            'billingData' => $billingData,
        ]);
    }

    public function sendInvoiceEmail(Request $request)
    {
        $data = $request->all();
        $cfdiData = $data['cfdiData'] ?? [];

        try {
            $this->facturamaFilesService->sendFilesByEmail($cfdiData, $data['email'] ?? null);

            return response()->json([
                'success' => true,
                'message' => 'Correo enviado con éxito',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el correo electrónico: ' . $e->getMessage(),
            ], 500);
        }
    }
}