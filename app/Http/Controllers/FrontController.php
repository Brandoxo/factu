<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

class FrontController extends Controller
{
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

    public function submitHotelForm(Request $request)
    {   // Manejar el envío del formulario del hotel y validar con Cloudbeds API
        $payload = $request->validate([
            'ticketFolio' => 'required',
            'checkOut' => 'required|date',
        ]);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('CLOUDBEDS_API_KEY'),
            'Accept'        => 'application/json',
        ])->get('https://api.cloudbeds.com/api/v1.3/getReservation', [
            'reservationID' => $payload['ticketFolio'],
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $reservation = $data['data'] ?? $data;
            $status = $reservation['status'] ?? null;

            // Determinar el campo de checkout esperado desde la API
            $expectedCheckOutRaw = $reservation['endDate']
                ?? $reservation['checkOut']
                ?? $reservation['checkout']
                ?? $reservation['checkOutDate']
                ?? $reservation['end_date']
                ?? null;

            // Normalizar fechas a Y-m-d para comparación confiable
            $providedCheckOut = null;
            $expectedCheckOut = null;
            try {
                $providedCheckOut = Carbon::parse($payload['checkOut'])->toDateString();
            } catch (\Throwable $e) {
                // Si la fecha provista no es válida, devolver error con detalles
                return response()->json([
                    'message' => 'La fecha de check-out enviada no es válida',
                    'provided_check_out' => $payload['checkOut'] ?? null,
                    'expected_check_out' => $expectedCheckOutRaw,
                ], 422);
            }

            if ($expectedCheckOutRaw) {
                try {
                    $expectedCheckOut = Carbon::parse($expectedCheckOutRaw)->toDateString();
                } catch (\Throwable $e) {
                    // Si la fecha esperada no es parseable, devolverla cruda para depurar
                    $expectedCheckOut = (string) $expectedCheckOutRaw;
                }
            }

            // Primero validar estado checkout
            if (!in_array(strtolower((string) $status), ['check_out', 'checkout', 'checked_out'])) {
                return response()->json([
                    'message' => 'La reserva no está en estado checkout',
                    'status'  => $status,
                    'data'    => $reservation,
                ], 422);
            }

            // Validar coincidencia de fecha de checkout si la API retorna una
            if ($expectedCheckOut && $expectedCheckOut !== $providedCheckOut) {
                return response()->json([
                    'message' => 'La fecha de check-out no coincide',
                    'expected_check_out' => $expectedCheckOut,
                    'provided_check_out' => $providedCheckOut,
                ], 422);
            }

            // Generar URL firmada para la página de facturación (10 minutos)
            $billingUrl = URL::temporarySignedRoute(
                'billing.form',
                now()->addMinutes(10),
                [
                    'reservationID' => $payload['ticketFolio'],
                    'checkOut'      => $providedCheckOut,
                ]
            );

            return response()->json([
                'message'     => 'Enviado y recibido correctamente',
                'data'        => $reservation,
                'billing_url' => $billingUrl,
            ]);
        }

        return response()->json([
            'message' => 'Error al llamar a la API',
            'status'  => $response->status(),
            'body'    => $response->json(),
        ], $response->status());
    }

    public function showBillingForm(Request $request, $reservationID)
    {
        // Requerir firma válida para evitar saltarse la validación previa
        if (! $request->hasValidSignature()) {
            return redirect()->route('home')->with('error', 'Enlace inválido o expirado');
        }

        $providedCheckOut = null;
        if ($request->filled('checkOut')) {
            try {
                $providedCheckOut = Carbon::parse($request->query('checkOut'))->toDateString();
            } catch (\Throwable $e) {
                return redirect()->route('home')->with('error', 'Fecha de check-out inválida');
            }
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('CLOUDBEDS_API_KEY'),
            'Accept'        => 'application/json',
        ])->get('https://api.cloudbeds.com/api/v1.3/getReservation', [
            'reservationID' => $reservationID,
        ]);

        if (! $response->successful()) {
            return redirect()->route('home')->with('error', 'Reserva no encontrada');
        }

        $data = $response->json();
        $reservation = $data['data'] ?? $data;
        $status = $reservation['status'] ?? null;

        $expectedCheckOutRaw = $reservation['endDate']
            ?? $reservation['checkOut']
            ?? $reservation['checkout']
            ?? $reservation['checkOutDate']
            ?? $reservation['end_date']
            ?? null;

        $expectedCheckOut = null;
        if ($expectedCheckOutRaw) {
            try {
                $expectedCheckOut = Carbon::parse($expectedCheckOutRaw)->toDateString();
            } catch (\Throwable $e) {
                $expectedCheckOut = (string) $expectedCheckOutRaw;
            }
        }

        if (! in_array(strtolower((string) $status), ['check_out', 'checkout', 'checked_out'])) {
            return redirect()->route('home')->with('error', 'La reserva no está en estado checkout');
        }

        if ($expectedCheckOut && $providedCheckOut && $expectedCheckOut !== $providedCheckOut) {
            return redirect()->route('home')->with('error', 'La fecha de check-out no coincide');
        }

        return Inertia::render('Billing/BillingForm', [
            'reservation' => $reservation,
        ]);
    }
};