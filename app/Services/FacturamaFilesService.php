<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceTax;
use App\Models\FiscalEntity;
use Illuminate\Http\JsonResponse;
use App\Models\TaxStamp;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FacturamaFilesService
{
    protected $username;
    protected $password;
    public function __construct()
    {
        $this->username = env('FACTURAMA_USERAGENT');
        $this->password = env('FACTURAMA_PASSWORD');
    }

    /**
     * Genera un folio único y seguro contra race conditions
     * Crea un registro temporal para reservar el folio antes de timbrar
     * @param array $basicData Datos básicos para crear el registro temporal
     * @return array ['folio' => '00001', 'invoice_id' => 123]
     */
    public function generateUniqueFolio($basicData)
    {
        return DB::transaction(function () use ($basicData) {
            // Obtener el máximo folio existente con lock exclusivo
            $maxFolio = Invoice::selectRaw('CAST(MAX(CAST(folio AS UNSIGNED)) AS UNSIGNED) as max_folio')
                ->lockForUpdate()
                ->first()?->max_folio ?? 0;

            // Incrementar el folio
            $nextFolio = ($maxFolio ?? 0) + 1;
            $folioFormatted = str_pad($nextFolio, 5, '0', STR_PAD_LEFT) . '-' . 'H'; // Sufijo para diferenciar folios de hotel

            // Prevenir duplicados incluso con múltiples peticiones simultáneas
            $invoice = Invoice::create([
                'fiscal_entity_id' => null, // Se actualizará después cuando se cree el fiscal_entity
                'reservation_id' => $basicData['reservation_id'] ?? '0',
                'order_id' => $basicData['order_id'] ?? 0,
                'folio' => $folioFormatted,
                'subtotal' => 0,
                'total' => 0,
                'pdf_path' => null,
                'xml_path' => null,
                'facturama_id' => null,
                'cfdi_uuid' => null,
                'status' => 'draft', // Estado temporal hasta que se timbre
                'payment_form' => null,
                'payment_method' => null,
                'use_cfdi' => null,
            ]);

            return [
                'folio' => $folioFormatted,
                'invoice_id' => $invoice->id,
            ];
        }, attempts: 3);
    }
    
    public function fetchCfdiFromApi($datosCfdi, $filteredRoomsAvailable, $optionsId, $request)
    {

    if(env('APP_ENV') === 'production'){
        $endpoint = env('FACTURAMA_PROD_ENDPOINT');
    } else {
        $endpoint = env('FACTURAMA_DEV_ENDPOINT');
    }

    $username = $this->username;
    $password = $this->password;

    $datosCfdi = $request->cfdiData;

    // Generar folio único y crear registro temporal para reservarlo
    $folioData = $this->generateUniqueFolio([
        'reservation_id' => $optionsId['reservationId'] ?? '0',
        'order_id' => $optionsId['orderId'] ?? 0,
    ]);
    
    $datosCfdi['Folio'] = $folioData['folio'];
    $request->merge(['reservedInvoiceId' => $folioData['invoice_id']]); // Agregar el ID de la factura reservada a la request para usarlo después
    $folio = $folioData['folio'];

    $currentReservationsId = [];
    foreach ($datosCfdi['Items'] as $index) {
        // Omitir items de cargos adicionales / servicios extras
        if ($index['Description'] === 'CARGOS ADICIONALES / SERVICIOS EXTRAS') {
            $currentReservationsId[] = $optionsId['reservationId'] .'-extras' ?? null;
            continue;
        }
        $currentReservationsId[] = $index['sub_reservation_id'];
    }
    $subReservationIDs = $filteredRoomsAvailable ?? null;

    $invoicedSubReservationIds = InvoiceItem::whereIn('sub_reservation_id', $subReservationIDs)
            ->distinct()
            ->pluck('sub_reservation_id')
            ->toArray();

    // Verificar si hay IDs duplicados facturados
    $duplicatedIds = array_intersect($currentReservationsId, $invoicedSubReservationIds);
    if (!empty($duplicatedIds)) {
        return response()->json([
            'success' => false,
            'message' => 'Algunos IDs de sub-reservación ya han sido facturados.',
            'duplicated_ids' => $duplicatedIds,
            'invoiced_ids' => $invoicedSubReservationIds,
            'requested_ids' => $currentReservationsId,
        ], 400);
    }

    $cfdiResponse = Http::withBasicAuth($username, $password)
        ->withoutVerifying()
        ->post("https://{$endpoint}/3/cfdis", $datosCfdi);

    if (!$cfdiResponse->successful()) {
        return response()->json([
            'success' => false,
            'message' => 'Error al timbrar CFDI'. ' Verifique que los datos sean correctos.',
            'facturama' => $cfdiResponse->json()
        ], 500);
    }

    // Almacenar el folio en la request para usarlo en storeCfdiData
    $request->merge(['generatedFolio' => $folio]);

    return $cfdiResponse;
    }

    public function storeCfdiFiles($cfdiResponse)
    {
    
    if(env('APP_ENV') === 'production'){
        $endpoint = env('FACTURAMA_PROD_ENDPOINT');
    } else {
        $endpoint = env('FACTURAMA_DEV_ENDPOINT');
    }

    $username = $this->username;
    $password = $this->password;

    $cfdi = $cfdiResponse->json();
    $id = $cfdi['Id'] ?? null;

    if (!$id) {
        return response()->json([
            'success' => false,
            'message' => 'CFDI no timbrado',
            'response' => $cfdi
        ], 500);
    }


    $xml = Http::withBasicAuth($username, $password)
        ->withoutVerifying()
        ->get("https://{$endpoint}/xml/payroll/{$id}");

    $pdf = Http::withBasicAuth($username, $password)
        ->withoutVerifying()
        ->get("https://{$endpoint}/pdfi/payroll/{$id}");

    if (!$xml->successful() || !$pdf->successful()) {
        return response()->json([
            'success' => false,
            'message' => 'No se pudieron descargar los archivos'
        ], 500);
    }

    Storage::put("cfdis/{$id}.xml", $xml->body());
    Storage::put("cfdis/{$id}.pdf", $pdf->body());

    return response()->json([
        'success' => true,
        'id' => $id,
        'files' => [
            'xml' => url("storage/cfdis/{$id}.xml"),
            'pdf' => url("storage/cfdis/{$id}.pdf"),
        ]
    ]);
    }

    public function storeCfdiData(array $data)
    {
        

        $cfdiData     = $data['cfdiData']['cfdiData'] ?? [];
        $cfdiResponse = $data['cfdiResponse'];
        $storageResponse  = $data['storageData'];
        $optionsId = $data['optionsId'] ?? null;
        $taxStampData = $cfdiResponse['Complement']['TaxStamp'] ?? [];
        $reservedInvoiceId = $data['reservedInvoiceId'] ?? null;
        $subReservationIDs = $data['extrasId']['subReservationIDs'] ?? null;
        $roomIDs = $data['extrasId']['roomIDs'] ?? null;

        $cfdiResponse = $cfdiResponse instanceof JsonResponse
            ? $cfdiResponse->json()
            : (is_array($cfdiResponse) ? $cfdiResponse : []);

        $storageResponse = $storageResponse instanceof JsonResponse
            ? $storageResponse->json()
            : (is_array($storageResponse) ? $storageResponse : []);

        $receiverData = $cfdiData['Receiver'] ?? [];

        $fullName = $receiverData['Name'] ?? 'Sin nombre';
        $nameParts = explode(' ', trim($fullName));
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[2] ?? $nameParts[1] ?? '';
        $simplifiedName = trim($firstName . ' ' . $lastName);

        $client = Client::firstOrCreate(
            [
                'internal_name' => $simplifiedName,
                'email' => $receiverData['Email'] ?? null,
            ]
        );
        
        $fiscalEntity = new FiscalEntity(
            [   
                'client_id'  => $client->id,
                'legal_name' => $receiverData['Name'] ?? 'Sin nombre',
                'rfc' => $receiverData['Rfc'] ?? 'XAXX010101000',
                'tax_regime' => $receiverData['FiscalRegime'] ?? null,
                'zip_code'   => $receiverData['TaxZipCode'] ?? null,
            ]
        );
        $fiscalEntity->save();
        
        $invoicePayload = [
            'fiscal_entity_id' => $fiscalEntity->id,
            'reservation_id' => $optionsId['reservationId'] ?? '0',
            'order_id' => $optionsId['orderId'] ?? 0,
            'subtotal' => $cfdiResponse['Subtotal'] ?? 0,
            'total' => $cfdiResponse['Total'] ?? 0,
            'pdf_path' => "cfdis/{$storageResponse['id']}.pdf",
            'xml_path' => "cfdis/{$storageResponse['id']}.xml",
            'facturama_id' => $cfdiResponse['Id'] ?? null,
            'cfdi_uuid' => $taxStampData['Uuid'] ?? null,
            'status' => $cfdiResponse['Status'] ?? 'draft',
            'payment_form' => $cfdiResponse['PaymentTerms'] ?? null,
            'payment_method' => $cfdiResponse['PaymentMethod'] ?? null,
            'use_cfdi' => $receiverData['CfdiUse'] ?? null,
        ];

        // Verificar si storeCfdiData recibió un ID de factura reservada para actualizar en lugar de crear una nueva
        $invoice = null;
        if ($reservedInvoiceId) {
            $invoice = Invoice::find($reservedInvoiceId);
            Log::info("Factura reservada encontrada para ID: {$reservedInvoiceId}", ['invoice' => $invoice]);
        }

        // Si se encontró la factura reservada y está en estado 'draft', actualizarla. De lo contrario, crear una nueva factura.
        if ($invoice && $invoice->status === 'draft') {
            $invoice->fill($invoicePayload);
            $invoice->save();
        } else {
            $invoice = new Invoice($invoicePayload);
            $invoice->save();
        }

        $taxStamp = new TaxStamp([
            'invoice_id' => $invoice->id,
            'cfdi_sign' => $taxStampData['CfdiSign'] ?? null,
            'rfc_prov_certif' => $taxStampData['RfcProvCertif'] ?? null,
            'sat_cert_number' => $taxStampData['SatCertNumber'] ?? null,
            'sat_sign' => $taxStampData['SatSign'] ?? null,
            'date_time' => $taxStampData['Date'] ?? null,
        ]);
        $taxStamp->save();

        $invoiceItemsData = $cfdiResponse['Items'] ?? [];

        foreach ($invoiceItemsData as $index => $itemData) {
            $invoiceItem = new InvoiceItem([
                'invoice_id' => $invoice->id,
                'product_code_sat' => $itemData['ProductCode'] ?? '',
                'unit_code_sat' => $itemData['UnitCode'] ?? '',
                'description' => $itemData['Description'] ?? '',
                'quantity' => $itemData['Quantity'] ?? 0,
                'unit_price' => $itemData['UnitValue'] ?? 0,
                'sub_reservation_id' => $cfdiData['Items'][$index]['sub_reservation_id'] ?? $optionsId['reservationId'] ?? null,
            ]);
            if($itemData['Description'] === 'CARGOS ADICIONALES / SERVICIOS EXTRAS'){
                $invoiceItem->sub_reservation_id = $optionsId['reservationId'] . '-extras' ?? null;
            }
            $invoiceItem->save();
            // Agregar impuestos al ítem
            $invoiceTaxIva = new InvoiceTax([
                'invoice_item_id' => $invoiceItem->id,
                'tax_type' => 'IVA',
                'rate' => 0.16,
                'amount' => $itemData['UnitValue'] * 0.16,
                'retention' => false,
            ]);
            $invoiceTaxIsh = new InvoiceTax([
                'invoice_item_id' => $invoiceItem->id,
                'tax_type' => 'ISH',
                'rate' => 0.05,
                'amount' => $itemData['UnitValue'] * 0.04,
                'retention' => false,
            ]);
            if($itemData['Description'] === 'CARGOS ADICIONALES / SERVICIOS EXTRAS'){
                // Solo guardar IVA para extras
                $invoiceTaxIva->save();
                // No agregar impuestos a los extras
                continue;
            }
            $invoiceTaxIva->save();
            $invoiceTaxIsh->save();
        }

        
        return [
            'success' => true,
            'message' => 'CFDI guardado correctamente',
            'invoice_id' => $invoice->id,
            'fiscal_entity_id' => $fiscalEntity->id,
            'cfdiData' => $cfdiData,
            'cfdiResponse' => $cfdiResponse,
            'storageResponse' => $storageResponse,
            'client_id' => $cfdiData['client_id'] ?? null,
        ];


    }

    public function sendFilesByEmail(array $data, $client_email)
    {   
        // storeResponse desglosado

        Mail::to($data['cfdiData']['Receiver']['Email'] ?? $client_email)->send(new \App\Mail\GenerateInvoice(
            $data
        ));

        return response()->json([
            'success' => true,
            'message' => 'Correo enviado correctamente',
        ]);
    }
}