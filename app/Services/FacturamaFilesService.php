<?php

namespace App\Services;

use App\Mail\GenerateInvoice;
use App\Mail\GenerateInvoiceToAdmin;
use App\Models\Client;
use App\Models\FiscalEntity;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceTax;
use App\Models\TaxStamp;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class FacturamaFilesService
{
    protected $username;
    protected $password;

    public function __construct()
    {
        $this->username = env('FACTURAMA_USERAGENT');
        $this->password = env('FACTURAMA_PASSWORD');
    }

    public function generateInvoice(array $validatedData): array
    {
        $cfdiPayload = $validatedData['cfdiData'] ?? [];
        $optionsId = is_array($validatedData['optionsId'] ?? null)
            ? $validatedData['optionsId']
            : [];

        if (!is_array($cfdiPayload) || empty($cfdiPayload)) {
            return [
                'success' => false,
                'message' => 'Los datos del CFDI son inválidos.',
                'status_code' => 422,
            ];
        }

        $requestedSubReservationIds = $this->extractRequestedSubReservationIds($cfdiPayload, $optionsId);

        if (empty($requestedSubReservationIds)) {
            return [
                'success' => false,
                'message' => 'No se pudo determinar qué sub-reservaciones se intentan facturar.',
                'status_code' => 422,
            ];
        }

        try {
            $preparedInvoice = $this->prepareInvoiceForProcessing($cfdiPayload, $optionsId, $requestedSubReservationIds);
            /** @var Invoice $invoice */
            $invoice = $preparedInvoice['invoice'];

            if ($preparedInvoice['action'] === 'recover') {
                return $this->recoverPendingInvoice($invoice);
            }

            $cfdiPayload['Folio'] = $invoice->folio;
            $this->updateDraftInvoice($invoice, $cfdiPayload, null);

            $cfdiResponse = $this->stampCfdi($cfdiPayload);

            if (!$cfdiResponse->successful()) {
                $message = 'Error al timbrar CFDI. Verifique que los datos sean correctos.';
                $this->markDraftFailure($invoice, $message, $cfdiPayload, $cfdiResponse->json());

                return [
                    'success' => false,
                    'message' => $message,
                    'facturama' => $cfdiResponse->json(),
                    'invoice_id' => $invoice->id,
                    'status_code' => 500,
                ];
            }

            $cfdiResponseData = $cfdiResponse->json();
            $invoice = $this->persistStampedInvoice($invoice, $cfdiPayload, $cfdiResponseData);

            return $this->completeStampedInvoice($invoice, $cfdiPayload, $cfdiResponseData);
        } catch (\DomainException $exception) {
            return [
                'success' => false,
                'message' => $exception->getMessage(),
                'status_code' => 409,
            ];
        } catch (\Throwable $exception) {
            Log::error('Error inesperado al generar factura', [
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al generar factura: ' . $exception->getMessage(),
                'status_code' => 500,
            ];
        }
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
            $maxFolio = Invoice::selectRaw('CAST(MAX(CAST(folio AS UNSIGNED)) AS UNSIGNED) as max_folio')
                ->lockForUpdate()
                ->first()?->max_folio ?? 0;

            $nextFolio = ($maxFolio ?? 0) + 1;
            $folioFormatted = str_pad($nextFolio, 5, '0', STR_PAD_LEFT) . '-H';

            $invoice = Invoice::create([
                'fiscal_entity_id' => null,
                'reservation_id' => $basicData['reservation_id'] ?? '0',
                'order_id' => $basicData['order_id'] ?? 0,
                'folio' => $folioFormatted,
                'subtotal' => 0,
                'total' => 0,
                'pdf_path' => null,
                'xml_path' => null,
                'facturama_id' => null,
                'cfdi_uuid' => null,
                'requested_sub_reservation_ids' => $basicData['requested_sub_reservation_ids'] ?? [],
                'request_payload' => $basicData['request_payload'] ?? null,
                'facturama_response' => null,
                'last_error' => null,
                'stamped_at' => null,
                'status' => 'draft',
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
        $preparedInvoice = $this->prepareInvoiceForProcessing(
            $request->cfdiData,
            is_array($optionsId) ? $optionsId : [],
            $this->extractRequestedSubReservationIds($request->cfdiData, is_array($optionsId) ? $optionsId : [])
        );

        if ($preparedInvoice['action'] === 'recover') {
            throw new \DomainException('La factura ya fue timbrada y quedó pendiente de finalizar. Reintente el flujo de recuperación.');
        }

        $invoice = $preparedInvoice['invoice'];
        $datosCfdi = $request->cfdiData;
        $datosCfdi['Folio'] = $invoice->folio;
        $this->updateDraftInvoice($invoice, $datosCfdi, null);

        $request->merge(['reservedInvoiceId' => $invoice->id]);

        return $this->stampCfdi($datosCfdi);
    }

    public function storeCfdiFiles($cfdiResponse)
    {
        $cfdi = $cfdiResponse instanceof JsonResponse
            ? $cfdiResponse->getData(true)
            : $cfdiResponse->json();

        $storageData = $this->downloadCfdiFiles($cfdi['Id'] ?? null);

        return response()->json($storageData, ($storageData['success'] ?? false) ? 200 : 500);
    }

    private function extractFacturamaContent($response)
    {
        $payload = $response->json();
        if (is_array($payload) && isset($payload['Content'])) {
            $content = $payload['Content'] ?? '';
            if (($payload['ContentEncoding'] ?? '') === 'base64') {
                $decoded = base64_decode($content, true);
                return $decoded === false ? '' : $decoded;
            }

            return is_string($content) ? $content : '';
        }

        return $response->body() ?? '';
    }

    public function storeCfdiData(array $data)
    {
        $cfdiData = $data['cfdiData'] ?? [];
        if (isset($cfdiData['cfdiData']) && is_array($cfdiData['cfdiData'])) {
            $cfdiData = $cfdiData['cfdiData'];
        }

        $cfdiResponse = $data['cfdiResponse'] ?? [];
        $storageResponse = $data['storageData'] ?? [];
        $optionsId = is_array($data['optionsId'] ?? null)
            ? $data['optionsId']
            : [];
        $reservedInvoiceId = $data['reservedInvoiceId'] ?? null;
        $invoice = $data['invoice'] ?? null;

        $cfdiResponse = $cfdiResponse instanceof JsonResponse
            ? $cfdiResponse->json()
            : (is_array($cfdiResponse) ? $cfdiResponse : []);

        $storageResponse = $storageResponse instanceof JsonResponse
            ? $storageResponse->json()
            : (is_array($storageResponse) ? $storageResponse : []);

        if (!$invoice instanceof Invoice && $reservedInvoiceId) {
            $invoice = Invoice::find($reservedInvoiceId);
        }

        if (!$invoice instanceof Invoice) {
            return [
                'success' => false,
                'message' => 'No se encontró la factura reservada para finalizar el CFDI.',
            ];
        }

        return $this->finalizeStoredInvoice($invoice, $cfdiData, $cfdiResponse, $storageResponse, $optionsId);
    }

    public function sendFilesByEmail(array $data, $client_email)
    {
        Mail::to($data['cfdiData']['Receiver']['Email'] ?? $client_email)->send(new GenerateInvoice($data));

        return response()->json([
            'success' => true,
            'message' => 'Correo enviado correctamente',
        ]);
    }

    public function sendFilesByEmailToAdmin(array $data)
    {
        Log::info('Enviando correo al administrador con los datos de la factura', ['data' => $data]);
        $adminEmail = 'facturacion@rondaminervahotel.com';
        Mail::to($adminEmail)->send(new GenerateInvoiceToAdmin($data));

        return response()->json([
            'success' => true,
            'message' => 'Correo enviado correctamente al administrador',
        ]);
    }

    private function prepareInvoiceForProcessing(array $cfdiPayload, array $optionsId, array $requestedSubReservationIds): array
    {
        $reservationId = (string) ($optionsId['reservationId'] ?? '0');
        $normalizedRequestedIds = $this->normalizeSubReservationIds($requestedSubReservationIds);

        return DB::transaction(function () use ($reservationId, $optionsId, $cfdiPayload, $normalizedRequestedIds) {
            $existingInvoices = Invoice::where('reservation_id', $reservationId)
                ->lockForUpdate()
                ->orderByDesc('id')
                ->get();

            foreach ($existingInvoices as $existingInvoice) {
                $storedIds = $this->extractReservedIdsFromInvoice($existingInvoice);

                if (!$this->hasSubReservationOverlap($normalizedRequestedIds, $storedIds)) {
                    continue;
                }

                if ($existingInvoice->status === 'active') {
                    throw new \DomainException('Esta reservación ya tiene una factura emitida para una o más sub-reservaciones seleccionadas.');
                }

                if ($existingInvoice->status === 'pending' || ($existingInvoice->status === 'draft' && $existingInvoice->facturama_id)) {
                    return [
                        'action' => 'recover',
                        'invoice' => $existingInvoice,
                    ];
                }

                if ($existingInvoice->status === 'draft') {
                    if ($this->sameSubReservationSet($normalizedRequestedIds, $storedIds)) {
                        $existingInvoice->fill([
                            'order_id' => $optionsId['orderId'] ?? $existingInvoice->order_id,
                            'request_payload' => $cfdiPayload,
                            'requested_sub_reservation_ids' => $normalizedRequestedIds,
                            'last_error' => null,
                        ]);
                        $existingInvoice->save();

                        return [
                            'action' => 'draft',
                            'invoice' => $existingInvoice->fresh(),
                        ];
                    }

                    throw new \DomainException('Existe una factura en borrador para parte de esta reservación. Reutilice ese borrador o elimine la inconsistencia antes de continuar.');
                }
            }

            $folioData = $this->generateUniqueFolio([
                'reservation_id' => $reservationId,
                'order_id' => $optionsId['orderId'] ?? 0,
                'requested_sub_reservation_ids' => $normalizedRequestedIds,
                'request_payload' => $cfdiPayload,
            ]);

            return [
                'action' => 'draft',
                'invoice' => Invoice::findOrFail($folioData['invoice_id']),
            ];
        }, attempts: 3);
    }

    private function stampCfdi(array $datosCfdi): Response
    {
        return Http::withBasicAuth($this->username, $this->password)
            ->withoutVerifying()
            ->post('https://' . $this->getFacturamaEndpoint() . '/3/cfdis', $datosCfdi);
    }

    private function persistStampedInvoice(Invoice $invoice, array $cfdiPayload, array $cfdiResponse): Invoice
    {
        $taxStampData = $cfdiResponse['Complement']['TaxStamp'] ?? [];

        $invoice->fill([
            'request_payload' => $cfdiPayload,
            'requested_sub_reservation_ids' => $this->extractRequestedSubReservationIds($cfdiPayload, [
                'reservationId' => $invoice->reservation_id,
            ]),
            'facturama_id' => $cfdiResponse['Id'] ?? $invoice->facturama_id,
            'facturama_response' => $cfdiResponse,
            'cfdi_uuid' => $taxStampData['Uuid'] ?? $invoice->cfdi_uuid,
            'subtotal' => $cfdiResponse['Subtotal'] ?? $invoice->subtotal,
            'total' => $cfdiResponse['Total'] ?? $invoice->total,
            'payment_form' => $cfdiResponse['PaymentForm'] ?? $cfdiPayload['PaymentForm'] ?? $invoice->payment_form,
            'payment_method' => $cfdiResponse['PaymentMethod'] ?? $cfdiPayload['PaymentMethod'] ?? $invoice->payment_method,
            'status' => 'pending',
            'stamped_at' => now(),
            'last_error' => null,
        ]);
        $invoice->save();

        return $invoice->fresh();
    }

    private function completeStampedInvoice(Invoice $invoice, array $cfdiPayload, array $cfdiResponse, bool $isRecovery = false): array
    {
        try {
            $storageData = $this->downloadCfdiFiles($invoice->facturama_id);
            if (!($storageData['success'] ?? false)) {
                throw new \RuntimeException($storageData['message'] ?? 'No se pudieron descargar los archivos del CFDI.');
            }

            $storeResponse = $this->finalizeStoredInvoice(
                $invoice,
                $cfdiPayload,
                $cfdiResponse,
                $storageData,
                [
                    'reservationId' => $invoice->reservation_id,
                    'orderId' => $invoice->order_id,
                ]
            );

            if (!($storeResponse['success'] ?? false)) {
                throw new \RuntimeException($storeResponse['message'] ?? 'No se pudo completar la persistencia del CFDI.');
            }

            $emailWarnings = $this->dispatchInvoiceEmails($storeResponse);

            session()->flash('billing_success_data', [
                'cfdiResponse' => $cfdiResponse,
                'storageResponse' => $storageData,
                'emailWarnings' => $emailWarnings,
            ]);

            $response = [
                'success' => true,
                'message' => $isRecovery
                    ? 'CFDI recuperado correctamente desde un estado pendiente.'
                    : 'CFDI generado correctamente',
                'successUrl' => URL::temporarySignedRoute('billing.success', now()->addMinutes(10)),
                'invoice_id' => $invoice->id,
            ];

            if (!empty($emailWarnings)) {
                $response['email_warnings'] = $emailWarnings;
            }

            return $response;
        } catch (\Throwable $exception) {
            $this->markPendingFailure($invoice, $exception->getMessage());

            Log::error('CFDI timbrado pero no finalizado', [
                'invoice_id' => $invoice->id,
                'facturama_id' => $invoice->facturama_id,
                'error' => $exception->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'El CFDI fue timbrado, pero no se pudo finalizar localmente. El registro quedó en estado pendiente para recuperación.',
                'invoice_id' => $invoice->id,
                'facturama_id' => $invoice->facturama_id,
                'status_code' => 500,
            ];
        }
    }

    private function finalizeStoredInvoice(Invoice $invoice, array $cfdiData, array $cfdiResponse, array $storageResponse, array $optionsId): array
    {
        $receiverData = $cfdiData['Receiver'] ?? [];
        $taxStampData = $cfdiResponse['Complement']['TaxStamp'] ?? [];

        $fullName = $receiverData['Name'] ?? 'Sin nombre';
        $nameParts = preg_split('/\s+/', trim($fullName)) ?: [];
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[2] ?? $nameParts[1] ?? '';
        $simplifiedName = trim($firstName . ' ' . $lastName);

        try {
            return DB::transaction(function () use ($invoice, $receiverData, $simplifiedName, $cfdiResponse, $storageResponse, $taxStampData, $optionsId, $cfdiData) {
                $client = Client::firstOrCreate([
                    'internal_name' => $simplifiedName,
                    'email' => $receiverData['Email'] ?? null,
                ]);

                $fiscalEntity = $invoice->fiscal_entity_id
                    ? FiscalEntity::find($invoice->fiscal_entity_id)
                    : null;

                if (!$fiscalEntity) {
                    $fiscalEntity = new FiscalEntity();
                }

                $fiscalEntity->fill([
                    'client_id' => $client->id,
                    'legal_name' => $receiverData['Name'] ?? 'Sin nombre',
                    'rfc' => $receiverData['Rfc'] ?? 'XAXX010101000',
                    'tax_regime' => $receiverData['FiscalRegime'] ?? null,
                    'zip_code' => $receiverData['TaxZipCode'] ?? null,
                ]);
                $fiscalEntity->save();

                $invoice->fill([
                    'fiscal_entity_id' => $fiscalEntity->id,
                    'reservation_id' => $optionsId['reservationId'] ?? $invoice->reservation_id,
                    'order_id' => $optionsId['orderId'] ?? $invoice->order_id,
                    'subtotal' => $cfdiResponse['Subtotal'] ?? 0,
                    'total' => $cfdiResponse['Total'] ?? 0,
                    'pdf_path' => 'cfdis/' . $storageResponse['id'] . '.pdf',
                    'xml_path' => 'cfdis/' . $storageResponse['id'] . '.xml',
                    'facturama_id' => $cfdiResponse['Id'] ?? $invoice->facturama_id,
                    'facturama_response' => $cfdiResponse,
                    'cfdi_uuid' => $taxStampData['Uuid'] ?? $invoice->cfdi_uuid,
                    'status' => 'active',
                    'payment_form' => $cfdiResponse['PaymentForm'] ?? $cfdiData['PaymentForm'] ?? $invoice->payment_form,
                    'payment_method' => $cfdiResponse['PaymentMethod'] ?? $cfdiData['PaymentMethod'] ?? $invoice->payment_method,
                    'use_cfdi' => $receiverData['CfdiUse'] ?? null,
                    'request_payload' => $cfdiData,
                    'requested_sub_reservation_ids' => $this->extractRequestedSubReservationIds($cfdiData, $optionsId),
                    'last_error' => null,
                    'stamped_at' => $invoice->stamped_at ?? now(),
                ]);
                $invoice->save();

                TaxStamp::updateOrCreate(
                    ['invoice_id' => $invoice->id],
                    [
                        'cfdi_sign' => $taxStampData['CfdiSign'] ?? null,
                        'rfc_prov_certif' => $taxStampData['RfcProvCertif'] ?? null,
                        'sat_cert_number' => $taxStampData['SatCertNumber'] ?? null,
                        'sat_sign' => $taxStampData['SatSign'] ?? null,
                        'date_time' => $taxStampData['Date'] ?? null,
                    ]
                );

                $existingItemIds = InvoiceItem::where('invoice_id', $invoice->id)->pluck('id');
                if ($existingItemIds->isNotEmpty()) {
                    InvoiceTax::whereIn('invoice_item_id', $existingItemIds)->delete();
                }
                InvoiceItem::where('invoice_id', $invoice->id)->delete();

                $invoiceItemsData = $cfdiResponse['Items'] ?? [];

                foreach ($invoiceItemsData as $index => $itemData) {
                    $invoiceItem = new InvoiceItem([
                        'invoice_id' => $invoice->id,
                        'product_code_sat' => $itemData['ProductCode'] ?? '',
                        'unit_code_sat' => $itemData['UnitCode'] ?? '',
                        'description' => $itemData['Description'] ?? '',
                        'quantity' => $itemData['Quantity'] ?? 0,
                        'unit_price' => $itemData['UnitValue'] ?? 0,
                        'sub_reservation_id' => $cfdiData['Items'][$index]['sub_reservation_id'] ?? ($optionsId['reservationId'] ?? null),
                    ]);

                    if (($itemData['Description'] ?? '') === 'CARGOS ADICIONALES / SERVICIOS EXTRAS') {
                        $invoiceItem->sub_reservation_id = ($optionsId['reservationId'] ?? null) . '-extras';
                    }

                    $invoiceItem->save();

                    $lineBase = ((float) ($itemData['UnitValue'] ?? 0)) * ((float) ($itemData['Quantity'] ?? 0));

                    $invoiceTaxIva = new InvoiceTax([
                        'invoice_item_id' => $invoiceItem->id,
                        'tax_type' => 'IVA',
                        'rate' => 0.16,
                        'amount' => $lineBase * 0.16,
                        'retention' => false,
                    ]);

                    if (($itemData['Description'] ?? '') === 'CARGOS ADICIONALES / SERVICIOS EXTRAS') {
                        $invoiceTaxIva->save();
                        continue;
                    }

                    $invoiceTaxIsh = new InvoiceTax([
                        'invoice_item_id' => $invoiceItem->id,
                        'tax_type' => 'ISH',
                        'rate' => 0.05,
                        'amount' => $lineBase * 0.05,
                        'retention' => false,
                    ]);

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
                    'client_id' => $client->id,
                ];
            }, attempts: 3);
        } catch (\Throwable $exception) {
            Log::error('Error al guardar CFDI', [
                'invoice_id' => $invoice->id,
                'error' => $exception->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al guardar CFDI: ' . $exception->getMessage(),
            ];
        }
    }

    private function recoverPendingInvoice(Invoice $invoice): array
    {
        if (empty($invoice->facturama_id) || empty($invoice->facturama_response) || empty($invoice->request_payload)) {
            return [
                'success' => false,
                'message' => 'Existe una factura pendiente, pero no tiene suficiente información para recuperarse automáticamente.',
                'invoice_id' => $invoice->id,
                'status_code' => 409,
            ];
        }

        return $this->completeStampedInvoice(
            $invoice,
            $invoice->request_payload,
            $invoice->facturama_response,
            true
        );
    }

    private function dispatchInvoiceEmails(array $storeResponse): array
    {
        $warnings = [];

        try {
            $this->sendFilesByEmail(
                $storeResponse,
                $storeResponse['cfdiData']['Receiver']['Email'] ?? null
            );
        } catch (\Throwable $exception) {
            Log::warning('No se pudo enviar correo al cliente', [
                'invoice_id' => $storeResponse['invoice_id'] ?? null,
                'error' => $exception->getMessage(),
            ]);
            $warnings[] = 'No se pudo enviar el correo al cliente.';
        }

        try {
            $this->sendFilesByEmailToAdmin($storeResponse);
        } catch (\Throwable $exception) {
            Log::warning('No se pudo enviar correo al administrador', [
                'invoice_id' => $storeResponse['invoice_id'] ?? null,
                'error' => $exception->getMessage(),
            ]);
            $warnings[] = 'No se pudo enviar el correo al administrador.';
        }

        return $warnings;
    }

    private function downloadCfdiFiles(?string $facturamaId): array
    {
        if (!$facturamaId) {
            return [
                'success' => false,
                'message' => 'CFDI no timbrado',
            ];
        }

        $cfdiType = 'issued';
        $endpoint = $this->getFacturamaEndpoint();

        $xml = Http::withBasicAuth($this->username, $this->password)
            ->withoutVerifying()
            ->get('https://' . $endpoint . '/api/Cfdi/xml/' . $cfdiType . '/' . $facturamaId);

        $pdf = Http::withBasicAuth($this->username, $this->password)
            ->withoutVerifying()
            ->get('https://' . $endpoint . '/api/Cfdi/pdf/' . $cfdiType . '/' . $facturamaId);

        $xmlBody = $this->extractFacturamaContent($xml);
        $pdfBody = $this->extractFacturamaContent($pdf);

        if (!$xml->successful() || !$pdf->successful() || $xmlBody === '' || $pdfBody === '') {
            return [
                'success' => false,
                'message' => 'No se pudieron descargar los archivos',
            ];
        }

        Storage::put('cfdis/' . $facturamaId . '.xml', $xmlBody);
        Storage::put('cfdis/' . $facturamaId . '.pdf', $pdfBody);

        return [
            'success' => true,
            'id' => $facturamaId,
            'files' => [
                'xml' => url('storage/cfdis/' . $facturamaId . '.xml'),
                'pdf' => url('storage/cfdis/' . $facturamaId . '.pdf'),
            ],
        ];
    }

    private function updateDraftInvoice(Invoice $invoice, array $cfdiPayload, ?string $errorMessage): void
    {
        $invoice->fill([
            'request_payload' => $cfdiPayload,
            'requested_sub_reservation_ids' => $this->extractRequestedSubReservationIds($cfdiPayload, [
                'reservationId' => $invoice->reservation_id,
            ]),
            'payment_form' => $cfdiPayload['PaymentForm'] ?? $invoice->payment_form,
            'payment_method' => $cfdiPayload['PaymentMethod'] ?? $invoice->payment_method,
            'last_error' => $errorMessage,
            'status' => 'draft',
        ]);
        $invoice->save();
    }

    private function markDraftFailure(Invoice $invoice, string $message, array $cfdiPayload, array $facturamaResponse = []): void
    {
        $invoice->fill([
            'request_payload' => $cfdiPayload,
            'facturama_response' => !empty($facturamaResponse) ? $facturamaResponse : null,
            'last_error' => $message,
            'status' => 'draft',
        ]);
        $invoice->save();
    }

    private function markPendingFailure(Invoice $invoice, string $message): void
    {
        $invoice->fill([
            'status' => 'pending',
            'last_error' => $message,
        ]);
        $invoice->save();
    }

    private function extractRequestedSubReservationIds(array $cfdiPayload, array $optionsId): array
    {
        $requestedIds = [];
        $reservationId = $optionsId['reservationId'] ?? null;

        foreach (($cfdiPayload['Items'] ?? []) as $item) {
            if (($item['Description'] ?? '') === 'CARGOS ADICIONALES / SERVICIOS EXTRAS') {
                if ($reservationId !== null) {
                    $requestedIds[] = $reservationId . '-extras';
                }
                continue;
            }

            if (!empty($item['sub_reservation_id'])) {
                $requestedIds[] = (string) $item['sub_reservation_id'];
            }
        }

        return $this->normalizeSubReservationIds($requestedIds);
    }

    private function normalizeSubReservationIds(array $subReservationIds): array
    {
        $normalized = array_values(array_unique(array_filter(array_map(
            static fn ($value) => $value === null ? null : trim((string) $value),
            $subReservationIds
        ))));

        sort($normalized);

        return $normalized;
    }

    private function extractReservedIdsFromInvoice(Invoice $invoice): array
    {
        $storedIds = $this->normalizeSubReservationIds($invoice->requested_sub_reservation_ids ?? []);

        if (!empty($storedIds)) {
            return $storedIds;
        }

        $itemIds = InvoiceItem::where('invoice_id', $invoice->id)
            ->pluck('sub_reservation_id')
            ->filter()
            ->map(static fn ($value) => trim((string) $value))
            ->values()
            ->all();

        return $this->normalizeSubReservationIds($itemIds);
    }

    private function hasSubReservationOverlap(array $left, array $right): bool
    {
        return !empty(array_intersect($left, $right));
    }

    private function sameSubReservationSet(array $left, array $right): bool
    {
        return $this->normalizeSubReservationIds($left) === $this->normalizeSubReservationIds($right);
    }

    private function getFacturamaEndpoint(): string
    {
        return env('APP_ENV') === 'production'
            ? env('FACTURAMA_PROD_ENDPOINT')
            : env('FACTURAMA_DEV_ENDPOINT');
    }
}