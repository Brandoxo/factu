<?php

namespace App\Http\Controllers\Pcbrestaurant;

use App\Http\Requests\Pcbrestaurant\ValidateSearchOrderRequest;
use App\Resources\Pcbrestaurant\GetOrders;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessFacturamaInvoice;
use App\Models\PcbresFiscalEntity;
use App\Models\PcbresInvoice;
use App\Resources\Pcbrestaurant\PcbrestaurantResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PcbrestaurantController extends Controller
{
    public function index() {
        $response = new GetOrders();
        $orders = $response->getAllOrders();

        // Build Order Datails Data for Cfdi Generation
        $payloadBuilder = new \App\Services\Billing\FacturamaPayloadBuilder();
        $customerFiscalData = [
            'Rfc' => '',
            'Name' => '',
            'email' => '',
            'TaxZipCode' => '',
            'FiscalRegime' => '',
            'CfdiUse' => '',
            'paymentMethod' => '',
            ];
        $payload = $payloadBuilder->buildFromPosOrder($orders['order'][0], $customerFiscalData);
        return response()->json(['message' => 'Pcbrestaurant API is working correctly', 'orders' => $orders, 'payload' => $payload]);
    }

    public function billing($ticketFolio) {
        $orderData = null;
        if ($ticketFolio) {
            $response = GetOrders::getOrderById($ticketFolio);
            if ($response) {
                $orderData = $response['order'];
            } else {
                return response()->json(['message' => "Orden no encontrada: $ticketFolio"], 404);
            }
        }
        return inertia('Billing/PcbresBillingForm', ['ticketFolio' => $ticketFolio, 'orderData' => $orderData]);
    }

    public function show(ValidateSearchOrderRequest $request, $ticketFolio) {
        $response = GetOrders::getOrderById($ticketFolio);
        if (!$response) {
            return response()->json(['message' => "Orden no encontrada: $ticketFolio"], 404);
        }
        return $response['order'];
        return response()->json(['error' => "Showing order with ticket folio: $ticketFolio", 'order' => $response]);
    }
    
    public function apiShow(ValidateSearchOrderRequest $request, $id) {
        $response = new PcbrestaurantResource();
        $order = $response->getOrderDetails($id);
        $billingUrl = $response->getBillingUrlForOrder($id, $order[0]['total'], $order[0]['date_time']);
        if (!$order) {
            return response()->json(['error' => "Orden no encontrada: $id"], 404);
        }
        return response()->json(['message' => "Showing order with ID: $id", 'order' => $order, 'billing_url' => $billingUrl]);
    }


    public function store(Request $request) 
    {
        $response = new PcbrestaurantResource();
        // 1. Validación estricta usando los nombres de tu objeto Vue
        $data = $request->validate([
            'ticketFolio' => 'required|integer',
            'paymentMethod' => 'required|string', // Viene del front como "01" o "01 - Efectivo"
            'rfc' => 'required|string|min:12|max:13',
            'razonSocial' => 'required|string',
            'email' => 'required|email',
            'codigoPostal' => 'required|string|size:5',
            'regimenFiscal' => 'required|string',
            'usoCfdi' => 'required|string',
        ]);

        $ticketId = $data['ticketFolio'];

        // 2. Candado Inteligente (Anti-doble timbrado)
        $invoice = PcbresInvoice::where('pos_order_id', $ticketId)->first();

        if ($invoice) {
            // Si está pendiente, procesando o ya timbrada, bloqueamos.
            if (in_array($invoice->status, ['pending', 'processing', 'stamped'])) {
                return response()->json(['message' => 'Este ticket ya está facturado o en proceso.'], 422);
            }
            // Si llega aquí, significa que existe pero su status es 'failed'.
        }
        // 3. La Fuente de la Verdad (Llamada al POS)
        $posResponse = $response->getOrderDetails($ticketId);
        if ($posResponse === null){
            return response()->json(['message' => 'Ticket no encontrado o no pagado.'], 404);
        }
        $orderData = $posResponse[0];

        // 4. Auto-corrección para Público en General (Reglas de CFDI 4.0)
        $lugarExpedicion = "44520"; // PON AQUÍ EL CP DE TU CUENTA SANDBOX/PRODUCCIÓN
        
        if (strtoupper($data['rfc']) === 'XAXX010101000') {
            $data['codigoPostal'] = $lugarExpedicion; // Obligatorio igual al emisor
            $data['regimenFiscal'] = '616';           // Sin obligaciones
            $data['usoCfdi'] = 'S01';                 // Sin efectos fiscales
        }

        // 5. Construimos el JSON masivo para Facturama
        $payload = $this->buildFacturamaPayload($orderData, $data, $lugarExpedicion);

        // 6. Transacción Atómica de Base de Datos
        DB::beginTransaction();
        try {
            
            PcbresFiscalEntity::updateOrCreate(
                ['rfc' => strtoupper($data['rfc'])],
                [
                    'legal_name' => strtoupper($data['razonSocial']),
                    'tax_regime' => $data['regimenFiscal'],
                    'zip_code' => $data['codigoPostal'],
                    'cfdi_use' => $data['usoCfdi'],
                    'email' => $data['email'],
                ]
            );
            
            // Guardamos el snapshot inmutable
            if ($invoice && $invoice->status === 'failed') {
                $invoice->update([
                    'status' => 'pending',
                    'stamped_fiscal_data' => $payload,
                    'error_log' => null,
                ]);
            } else {
                $invoice = PcbresInvoice::create([
                    'pos_order_id' => $ticketId,
                    'status' => 'pending',
                    'subtotal' => $this->calculateSubtotal($orderData),
                    'total' => $orderData['total'],
                    'stamped_fiscal_data' => $payload,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error interno al preparar la factura.'], 500);
        }

        // 7. Lanzamos el Job a la cola para que se pelee con el SAT en el fondo
        ProcessFacturamaInvoice::dispatch($invoice);
        
        return response()->json([
            'message' => 'Tu factura está en proceso. Te llegará al correo en unos minutos.',
            'invoice_id' => $invoice->id
            ], 202);
    }

    // --- Helpers Privados ---

    private function buildFacturamaPayload(array $orderData, array $data, string $lugarExpedicion): array
    {
        $items = [];
        foreach ($orderData['orderDetails'] as $detail) {
            $cantidad = (float) $detail['quantity'];
            $precioUnitarioConIva = (float) $detail['unit_price']; 

            // 1. Extraemos el valor unitario SIN IVA (Usa 4 a 6 decimales para la precisión del SAT)
            $unitPriceSinIva = round($precioUnitarioConIva / 1.16, 6);

            // 2. El Subtotal de la línea es (Precio Unitario * Cantidad) redondeado a 2 decimales
            $subtotalLinea = round($unitPriceSinIva * $cantidad, 2);

            // 3. El IVA de la línea se calcula estrictamente sobre el $subtotalLinea
            $taxAmount = round($subtotalLinea * 0.16, 2);

            // 4. El Total exacto que el SAT espera ver
            $totalLinea = round($subtotalLinea + $taxAmount, 2);

            $items[] = [
                "ProductCode" => "90101501", 
                "UnitCode" => "E48",
                "Unit" => "Servicio",
                "Description" => $detail['product']['name'],
                "Quantity" => $cantidad,
                "UnitPrice" => $unitPriceSinIva, // Facturama acepta los 6 decimales aquí
                "Subtotal" => $subtotalLinea,
                "TaxObject" => "02",
                "Total" => $totalLinea,
                "Taxes" => [[
                    "Name" => "IVA",
                    "Rate" => 0.16,
                    "Total" => $taxAmount,
                    "Base" => $subtotalLinea,
                    "IsRetention" => false
                ]]
            ];
        }

        // Si el front manda "01 - Efectivo", extraemos solo el "01"
        $formaPago = substr($data['paymentMethod'], 0, 2);

        return [
            "Receiver" => [
                "Name" => strtoupper($data['razonSocial']),
                "CfdiUse" => substr($data['usoCfdi'], 0, 3), // Seguridad por si manda "G03 - Gastos..."
                "Rfc" => strtoupper($data['rfc']),
                "FiscalRegime" => substr($data['regimenFiscal'], 0, 3),
                "TaxZipCode" => $data['codigoPostal']
            ],
            "CfdiType" => "I",
            "PaymentForm" => $formaPago,
            "PaymentMethod" => "PUE",
            "ExpeditionPlace" => $lugarExpedicion,
            "Exportation" => "01",
            "Items" => $items
        ];
    }

    private function calculateSubtotal(array $orderData): float
    {
        return round((float) $orderData['total'] / 1.16, 2);
    }
}
