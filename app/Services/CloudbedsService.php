<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
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
        $apiKey = env('CLOUDBEDS_API_KEY');
        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'Accept'        => 'application/json',
        ])->get('https://api.cloudbeds.com/api/v1.2/getReservation', [
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

    public function extractAllRooms($reservation)
    {
        $allRoomIds = [];
        if ($reservation['balanceDetailed']['additionalItems'] > 0) {
            $allRoomIds[] = $reservation['reservationID'] . '-extras';
        }
        if (isset($reservation['assigned']) && is_array($reservation['assigned'])) {
            foreach ($reservation['assigned'] as $assignment) {
                $roomId = $assignment['subReservationID'] ?? $assignment['subReservationID'] ?? null;
                if ($roomId) {
                    $allRoomIds[] = $roomId;
                }
            }
        }
        $invoicedSubReservationIds = InvoiceItem::whereIn('sub_reservation_id', $allRoomIds)
            ->whereIn('invoice_id', function ($query) use ($reservation) {
                $query->select('id')
                      ->from('invoices')
                      ->where('reservation_id', $reservation['reservationID'])
                      ->where('status', 'active');
            })
            ->distinct()
            ->pluck('sub_reservation_id')
            ->toArray();
        return array_diff($allRoomIds, $invoicedSubReservationIds);
    }

    public function getReservationData($ticketFolio, $checkOut)
    {
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

        $today = substr(date('Y-m-d'), 5, 6);
        $cfdiDate = substr($reservation['endDate'], 5, 6) ?? null;
        if ($cfdiDate !== $today) {
            return response()->json([
                'success' => false,
                'message' => 'La facturación solo está permitida para el mes en curso del checkout.',
                'cfdi_date' => $cfdiDate,
                'current_date' => $today,
                    ], 400);
        }

        // Extraer TODOS los room_id de la respuesta de Cloudbeds
        $allRoomIds = [];
        if ($reservation['balanceDetailed' === 0] ?? false) {
            $allRoomIds[] = $reservation['reservationID'] . '-extras';
        }
        if (isset($reservation['assigned']) && is_array($reservation['assigned'])) {
            foreach ($reservation['assigned'] as $assignment) {
                $roomId = $assignment['subReservationID'] ?? $assignment['subReservationID'] ?? null;
                if ($roomId) {
                    $allRoomIds[] = $roomId;
                }
            }
        }

        if (empty($allRoomIds)) {
            return response()->json([
                'message' => 'No se pudo obtener ningún ID de habitación de la reservación',
                'data'    => $reservation,
            ], 422);
        }

        // Obtener habitaciones que YA TIENEN factura activa
        // Buscar invoices activas de esta reservación que tengan items con sub_reservation_id en $allRoomIds
        $invoicedRoomIds = Invoice::where('reservation_id', $ticketFolio)
                                   ->where('status', 'active');
        $invoicedRoomIds = InvoiceItem::whereIn('sub_reservation_id', $allRoomIds)
                                   ->whereIn('invoice_id', $invoicedRoomIds->pluck('id'))
                                   ->distinct()
                                   ->pluck('sub_reservation_id')
                                   ->toArray();

        // Identificar habitaciones SIN factura
        $availableRoomIds = array_diff($allRoomIds, $invoicedRoomIds);

        // Si NO hay habitaciones disponibles para facturar, bloquear
        if (empty($availableRoomIds)) {
            return response()->json([
                'message' => 'Todas las habitaciones de esta reservación ya tienen facturas emitidas',
                'invoiced_rooms' => $invoicedRoomIds,
            ], 422);
        }

        // Generar URL firmada para la página de facturación
        $billingUrl = URL::temporarySignedRoute(
            'billing.form',
            now()->addMinutes(10),
            [
                'reservationID' => $ticketFolio,
                'availableRoomIds' => $availableRoomIds,
                'checkOut'      => $providedCheckOut,
            ]
        );

        return response()->json([
            'message'     => 'Enviado y recibido correctamente',
            'data'        => $reservation,
            'all_rooms'   => $allRoomIds,
            'invoiced_rooms' => $invoicedRoomIds,
            'available_rooms' => $availableRoomIds,
            'billing_url' => $billingUrl,
        ]);
    }
}