<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;


use Exception;

class CloudbedsService
{
    /**
     * Obtener y validar datos de reserva desde Cloudbeds API
     */
    public function fetchReservationFromAPI($reservationID)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('CLOUDBEDS_API_KEY'),
            'Accept'        => 'application/json',
        ])->get('https://api.cloudbeds.com/api/v1.3/getReservation', [
            'reservationID' => $reservationID,
        ]);

        return $response;
    }

    /**
     * Validar datos de la reserva y checkout
     */
    public function validateReservationData($reservation, $providedCheckOut = null)
    {
        $status = $reservation['status'] ?? null;

        // Determinar el campo de checkout esperado desde la API
        $expectedCheckOutRaw = $reservation['endDate']
            ?? $reservation['checkOut']
            ?? $reservation['checkout']
            ?? $reservation['checkOutDate']
            ?? $reservation['end_date']
            ?? null;

        // Normalizar fechas a Y-m-d para comparación confiable
        $expectedCheckOut = null;
        if ($expectedCheckOutRaw) {
            try {
                $expectedCheckOut = Carbon::parse($expectedCheckOutRaw)->toDateString();
            } catch (\Throwable $e) {
                // Si la fecha esperada no es parseable, devolverla cruda para depurar
                $expectedCheckOut = (string) $expectedCheckOutRaw;
            }
        }

        // Validar estado checkout
        if (!in_array(strtolower((string) $status), ['check_out', 'checkout', 'checked_out'])) {
            return [
                'valid' => false,
                'error' => 'La reserva no está en estado checkout',
                'status' => $status,
            ];
        }

        // Validar coincidencia de fecha de checkout si se proporciona
        if ($providedCheckOut && $expectedCheckOut && $expectedCheckOut !== $providedCheckOut) {
            return [
                'valid' => false,
                'error' => 'La fecha de check-out no coincide',
                'expected_check_out' => $expectedCheckOut,
                'provided_check_out' => $providedCheckOut,
            ];
        }

        return [
            'valid' => true,
            'expected_check_out' => $expectedCheckOut,
            'reservation' => $reservation,
        ];
    }

    public function getReservationData($ticketFolio, $checkOut)
    {
        $existingInvoice = Invoice::where('reservation_id', $ticketFolio)
                                    ->where('status', 'stamped')
                                    ->first();
        if ($existingInvoice) {
            throw new Exception('Esta reserva ya tiene una factura emitida.');
        }

        // Normalizar fecha de checkout proporcionada
        $providedCheckOut = null;
        try {
            $providedCheckOut = Carbon::parse($checkOut)->toDateString();
        } catch (\Throwable $e) {
            // Si la fecha provista no es válida, devolver error con detalles
            return response()->json([
                'message' => 'La fecha de check-out enviada no es válida',
                'provided_check_out' => $checkOut ?? null,
            ], 422);
        }

        $response = $this->fetchReservationFromAPI($ticketFolio);

        if (!$response->successful()) {
            return response()->json([
                'message' => 'Error al llamar a la API',
                'status'  => $response->status(),
                'body'    => $response->json(),
            ], $response->status());
        }

        $data = $response->json();
        $reservation = $data['data'] ?? $data;

        $validation = $this->validateReservationData($reservation, $providedCheckOut);

        if (!$validation['valid']) {
            return response()->json([
                'message' => $validation['error'],
                'status'  => $validation['status'] ?? null,
                'data'    => isset($validation['expected_check_out']) ? null : $reservation,
            ], 422);
        }

        // Generar URL firmada para la página de facturación (10 minutos)
        $billingUrl = URL::temporarySignedRoute(
            'billing.form',
            now()->addMinutes(10),
            [
                'reservationID' => $ticketFolio,
                'checkOut'      => $providedCheckOut,
            ]
        );

        return response()->json([
            'message'     => 'Enviado y recibido correctamente',
            'data'        => $reservation,
            'billing_url' => $billingUrl,
        ]);
    }
}