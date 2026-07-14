<?php

namespace App\Http\Controllers\Pcbrestaurant;

use App\Http\Requests\Pcbrestaurant\ValidateSearchOrderRequest;
use App\Resources\Pcbrestaurant\PcbrestaurantResource;
use App\Resources\Pcbrestaurant\GetOrders;
use App\Resources\Invoices\GetInvoice;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessFacturamaInvoice;
use Illuminate\Support\Facades\Log;
use App\Models\PcbresFiscalEntity;
use App\Models\PcbresInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Services\FacturamaFilesService;
use Illuminate\Validation\ValidationException;
class PcbrestaurantController extends Controller
{

    protected FacturamaFilesService $facturamaFilesService;
    
    public function __construct(FacturamaFilesService $facturamaFilesService)
    {
        $this->facturamaFilesService = $facturamaFilesService;
    }

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
    
    public function apiShow(ValidateSearchOrderRequest $request, int $id) {


        $invoice = new GetInvoice;
        $invoice = $invoice->GetInvoiceIfExists($id);
        if ($invoice) {
            return response()->json([
                'message' => "Orden encontrada con factura existente.",
                'order' => $invoice->pos_order_id,
                'status' => $invoice->status
            ]);
        }
        $response = new PcbrestaurantResource();
        $order = $response->getOrderDetails($id);
        if (!$order) {
            return response()->json(['error' => "Orden no encontrada: $id"], 404);
        }
        // Si la orden existe, comparar que date, ticketFolio y totalAmount coincidan con lo que se envió en la URL (Esto es un candado extra para evitar que alguien intente adivinar URLs)
        $params = request()->all();
        if (!$this->ValidateOrderData($order, $params)) {
        $order[0]['date_time'] = substr($order[0]['date_time'], 0, 10);
        $params['date'] = substr($params['date'], 0, 10);
            return response()->json([
                'error' => "Los parámetros de la orden no coinciden con los enviados.",
                'order_data' => $order,
                'request_data' => $params
                ], 400);
        }
        $billingUrl = $response->getBillingUrlForOrder($id, $order[0]['total'], $order[0]['date_time']);
        return response()->json([
            'message' => "Showing order with ID: $id",
            'order' => $order,
            'billing_url' => $billingUrl,
            'status' => 'ok'
        ]);
    }

    public function ValidateOrderData($orderData, $requestData) {
        // Normalizamos el formato de fecha para comparar (asumiendo que viene como "2024-08-01 11:00:00" y lo convertimos a "2024-08-01")
        $orderDate = substr($orderData[0]['date_time'], 0, 10);
        $requestDate = substr($requestData['date'], 0, 10);
        if ($requestDate != $orderDate) {
            return false;
        }
        if ($requestData['ticketFolio'] != $orderData[0]['id']) {
            return false;
        }
        if ($requestData['totalAmount'] !== $orderData[0]['total']) {
            return false;
        }
        return true;
    }


    public function store(Request $request) 
    {

        //return redirect()->back()->with('message', '¡Factura generada con éxito! Revisa tu correo para descargarla.');

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
            // ELIMINAMOS 'failed' DEL ARREGLO PARA PERMITIR REINTENTOS
            if (in_array($invoice->status, ['pending', 'processing', 'stamped'])) {
                
                // Obtenemos los datos frescos de la orden
                $posResponse = $response->getOrderDetails($ticketId);

                // BYPASS: Renderizamos la vista de vuelta inyectando el error explícitamente
                return inertia('Billing/PcbresBillingForm', [
                    'ticketFolio' => $ticketId,
                    'orderData' => $posResponse,
                    'error' => 'Ya existe una factura en proceso o generada exitosamente para este ticket.'
                ]);
            }
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

        // 7. EJECUCIÓN SÍNCRONA (Adiós Job, hola tiempo de espera)
        try {
            // Cambiamos el estado a procesando (aunque sea por unos segundos)
            $invoice->update(['status' => 'processing']);

            $service = new \App\Services\Facturama\FacturamaService;
            $response = $service->stampInvoice($payload);
            
            $uuid = $response['Complement']['TaxStamp']['Uuid'];
            $facturamaId = $response['Id'];

            $serie = $response['Serie'] ?? '';
            $folioNumero = $response['Folio'] ?? '';
            $cfdiFolioFinal = trim($serie . ' ' . $folioNumero);

            // Descargamos los archivos en caliente
            $pdfContent = $service->downloadInvoiceFile($facturamaId, 'pdf');
            $xmlContent = $service->downloadInvoiceFile($facturamaId, 'xml');

            $pdfPath = "cfdis/{$facturamaId}.pdf";
            $xmlPath = "cfdis/{$facturamaId}.xml";
            
            Storage::disk('local')->put($pdfPath, $pdfContent);
            Storage::disk('local')->put($xmlPath, $xmlContent);

            $storageData = [
                'pdf' => $pdfPath,
                'xml' => $xmlPath,
            ];

            $emailWarnings = [];
            
            session()->flash('billing_success_data', [
                'cfdiResponse' => $response,
                'storageResponse' => $storageData,
                'emailWarnings' => $emailWarnings,
            ]);
            // Actualizamos la base de datos con el triunfo
            $invoice->update([
                'status' => 'stamped',
                'cfdi_folio' => $cfdiFolioFinal,
                'facturama_id' => $facturamaId,
                'pdf_path' => $pdfPath,
                'xml_path' => $xmlPath,
                'error_log' => null,
            ]);

            // Disparamos el correo (Esto sumará un par de segundos más a la espera del usuario)
            if (!empty($data['email'])) {
                $cfdiData = session('billing_success_data');
            try {
                $this->facturamaFilesService->sendFilesByEmail($cfdiData, $data['email'] ?? null);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al enviar el correo electrónico: ' . $e->getMessage(),
                ], 500);
            }
            }

            // Caso exitoso
            return redirect()->route('pcbrestaurant.invoice.success', [
                    'billingData' => session('billing_success_data'),
                ])->with('message', '¡Factura generada con éxito! Revisa tu correo para descargarla.'); 
            
            // return redirect()->back()->with('message', '¡Factura generada con éxito! Revisa tu correo para descargarla.');

        } catch (\Exception $e) {
            // Si el SAT o Facturama se quejan, lo registramos y le avisamos al usuario al instante
            $invoice->update([
                'status' => 'failed',
                'error_log' => $e->getMessage(),
            ]);

            // BYPASS: Renderizamos la vista inyectando el error del SAT
            return inertia('Billing/PcbresBillingForm', [
                'ticketFolio' => $ticketId,
                'orderData' => [$orderData], 
                'error' => 'El SAT rechazó la factura: ' . $e->getMessage()
            ]);
        }

        // Caso ya facturada
                    return redirect()->route('pcbrestaurant.invoice.success', [
                    'billingData' => session('billing_success_data'),
                ])->with('message', '¡Factura generada con éxito! Revisa tu correo para descargarla.'); 
        return response()->json([
            'message' => 'Tu factura está en proceso. Te llegará al correo en unos minutos.',
            'invoice_id' => $invoice->id
            ], 202);
    }

    // --- Helpers Privados ---

private function buildFacturamaPayload(array $orderData, array $data, string $lugarExpedicion): array
{
    bcscale(8); // 8 decimales de precisión interna
    
    $metaTotal = (string) $orderData['total'];
    $itemsProcesados = [];
    $sumatoriaTotales = '0';
    
    // 1. Calcular cada línea derivando subtotal desde precio con IVA
    foreach ($orderData['orderDetails'] as $detail) {
        $cantidad = (string) $detail['quantity'];
        $precioConIva = (string) $detail['unit_price'];
        
        // Subtotal unitario sin IVA, con 6 decimales (máximo que permite el SAT)
        $unitPriceSinIva = bcdiv($precioConIva, '1.16', 6);
        
        // Subtotal de línea con 6 decimales (sin redondear todavía)
        $subtotalLineaPreciso = bcmul($unitPriceSinIva, $cantidad, 6);
        
        // Redondeo a 2 decimales para reporte (lo que va al CFDI)
        $subtotalLinea = $this->bcround($subtotalLineaPreciso, 2);
        
        // Impuesto: Base × Rate, redondeado a 2
        $taxAmount = $this->bcround(bcmul($subtotalLinea, '0.16', 6), 2);
        
        // Total de línea
        $totalLinea = bcadd($subtotalLinea, $taxAmount, 2);
        
        $itemsProcesados[] = [
            'detail' => $detail,
            'cantidad' => $cantidad,
            'unitPriceSinIva' => $unitPriceSinIva,
            'subtotalLinea' => $subtotalLinea,
            'taxAmount' => $taxAmount,
            'totalLinea' => $totalLinea
        ];
        
        $sumatoriaTotales = bcadd($sumatoriaTotales, $totalLinea, 2);
    }
    
    // 2. Ajuste por diferencia: absorber en el último item
    $diferencia = bcsub($metaTotal, $sumatoriaTotales, 2);
    
    if (bccomp($diferencia, '0', 2) !== 0) {
        $lastIdx = count($itemsProcesados) - 1;
        $item = &$itemsProcesados[$lastIdx];
        
        // Nuevo total de línea ajustado
        $nuevoTotal = bcadd($item['totalLinea'], $diferencia, 2);
        
        // Derivar nuevo subtotal e impuesto desde el total ajustado
        // Total = Base + (Base × 0.16) = Base × 1.16
        // Por lo tanto Base = Total / 1.16
        $nuevoSubtotal = $this->bcround(bcdiv($nuevoTotal, '1.16', 6), 2);
        $nuevoTax = bcsub($nuevoTotal, $nuevoSubtotal, 2);
        
        // Reemplazar valores
        $item['subtotalLinea'] = $nuevoSubtotal;
        $item['taxAmount'] = $nuevoTax;
        $item['totalLinea'] = $nuevoTotal;
        $item['unitPriceSinIva'] = bcdiv($nuevoSubtotal, $item['cantidad'], 6);
        unset($item);
    }
    // 3. Mapear al formato Facturama (convertir strings a float al final)
    $itemsParaFacturama = [];
    foreach ($itemsProcesados as $item) {
        $itemsParaFacturama[] = [
            "ProductCode" => "90101501",
            "UnitCode" => "E48",
            "Unit" => "Servicio",
            "Description" => $item['detail']['product']['name'],
            "Quantity" => (float) $item['cantidad'],
            "UnitPrice" => (float) $item['unitPriceSinIva'],
            "Subtotal" => (float) $item['subtotalLinea'],
            "TaxObject" => "02",
            "Total" => (float) $item['totalLinea'],
            "Taxes" => [[
                "Name" => "IVA",
                "Rate" => 0.16,
                "Total" => (float) $item['taxAmount'],
                "Base" => (float) $item['subtotalLinea'],
                "IsRetention" => false
            ]]
        ];
    }
    
    $formaPago = substr($data['paymentMethod'], 0, 2);
    Log::info('Payload final a enviar', ['payload' => $itemsParaFacturama]);
    return [
        "Receiver" => [
            "Name" => strtoupper($data['razonSocial']),
            "CfdiUse" => substr($data['usoCfdi'], 0, 3),
            "Rfc" => strtoupper($data['rfc']),
            "FiscalRegime" => substr($data['regimenFiscal'], 0, 3),
            "TaxZipCode" => $data['codigoPostal']
        ],
        "CfdiType" => "I",
        "ExpeditionPlace" => $lugarExpedicion,
        "Exportation" => "01",
        "Currency" => "MXN",
        "Items" => $itemsParaFacturama,
        "Serie" => "R",
        "PaymentForm" => $formaPago,
        "PaymentMethod" => "PUE",
    ];
}

// Helper: BCMath no tiene round nativo, lo implementamos
private function bcround(string $number, int $precision = 2): string
{
    if (bccomp($number, '0', $precision + 1) >= 0) {
        return bcadd($number, '0.' . str_repeat('0', $precision) . '5', $precision);
    }
    return bcsub($number, '0.' . str_repeat('0', $precision) . '5', $precision);
}
    
    public function invoiceSuccess()
    {
        return inertia('Billing/PcbresBillingSuccess',[
            'billingData' => session('billing_success_data'),
        ]);
    }

    private function calculateSubtotal(array $orderData): float
    {
        return round((float) $orderData['total'] / 1.16, 2);
    }
}
