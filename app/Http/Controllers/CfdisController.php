<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FacturamaFilesService;
use App\Http\Requests\CfdiFormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use Laravel\Pail\File;

class CfdisController extends Controller
{
    protected FacturamaFilesService $facturamaFilesService;

    public function __construct(FacturamaFilesService $facturamaFilesService)
    {
        $this->facturamaFilesService = $facturamaFilesService;
    }
    
    public function store(array $data)
    {

        try {
 
            $storeResponse = $this->facturamaFilesService->storeCfdiData($data);
            return $storeResponse;
 
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Excepción al almacenar CFDI: ' . $e->getMessage(),
            ], 500);
        }
 
    }
    
   public function generateInvoice(CfdiFormRequest $request)
{
    $cfdiData = $request->validated();
    $filteredRoomsAvailable = $cfdiData['filteredRoomsAvailable'] ?? [];
    $optionsId = $cfdiData['optionsId'] ?? null;
    
    try {
        // Usar transacción para asegurar que si falla el timbrado, se revierta todo lo guardado en BD
        return DB::transaction(function () use ($request, $cfdiData, $filteredRoomsAvailable, $optionsId) {
            
            // Obtener CFDI desde Facturama API (esto crea un registro temporal en BD)
            $cfdiResponse = $this->facturamaFilesService->fetchCfdiFromApi($cfdiData, $filteredRoomsAvailable, $optionsId, $request);
            
            // Verificar si la respuesta es un error de JsonResponse
            if ($cfdiResponse instanceof JsonResponse) {
                // Lanzar excepción para hacer rollback de la transacción
                throw new \Exception($cfdiResponse->getData(true)['message'] ?? 'Error al timbrar CFDI');
            }
            
            if ($cfdiResponse->failed()) {
                // Lanzar excepción para hacer rollback de la transacción
                $errorMessage = $cfdiResponse->json()['message'] ?? 'Error al obtener CFDI desde Facturama';
                throw new \Exception($errorMessage);
            }

            // Almacenar archivos del CFDI
            $storageResponse = $this->facturamaFilesService->storeCfdiFiles($cfdiResponse);
            
            // Normalizar datos de almacenamiento
            if ($storageResponse instanceof JsonResponse) {
                $data = $storageResponse->getData(true);
                if (!($data['success'] ?? true)) {
                    throw new \Exception($data['message'] ?? 'Error al almacenar archivos del CFDI');
                }
            }

            // Normalizar datos para almacenamiento
            $storageData = $storageResponse instanceof JsonResponse
                ? $storageResponse->getData(true)
                : $storageResponse->json();

            $cfdiResponse_Array = $cfdiResponse->json();
            
            // Guardar datos del CFDI en BD
            if (!$storageData || !($storageData['success'] ?? false)) {
                throw new \Exception('Error en los datos de almacenamiento');
            }

            $storeResponse = $this->store([
                'cfdiData' => $cfdiData,
                'cfdiResponse' => $cfdiResponse_Array,
                'storageData' => $storageData,
                'optionsId' => $cfdiData['optionsId'] ?? null,
                'extrasId' => $cfdiData['extrasId'] ?? null,
                'reservedInvoiceId' => $request->input('reservedInvoiceId'),
            ]);
            
            // Verificar si store() retornó un error (JsonResponse)
            if ($storeResponse instanceof JsonResponse) {
                throw new \Exception($storeResponse->getData(true)['message'] ?? 'Error al guardar CFDI en BD');
            }

            if (!($storeResponse['success'] ?? false)) {
                throw new \Exception($storeResponse['message'] ?? 'Error al guardar CFDI');
            }

            // Enviar emails
            $responseByEmail = $this->facturamaFilesService->sendFilesByEmail(
                $storeResponse, $storeResponse['cfdiData']['Receiver']['Email'] ?? null
            );

            $responseByEmailAdmin = $this->facturamaFilesService->sendFilesByEmailToAdmin(
                $storeResponse
            );

            session()->flash('billing_success_data', [
                'cfdiResponse' => $cfdiResponse_Array,
                'storageResponse' => $storageData,
                'emailSent' => $responseByEmail,
                'emailSentToAdmin' => $responseByEmailAdmin,
            ]);

            // Generar URL firmada para la página de éxito
            $successUrl = URL::temporarySignedRoute('billing.success', now()->addMinutes(10));

            return response()->json([
                'success' => true,
                'message' => 'CFDI generado correctamente',
                'successUrl' => $successUrl,
            ]);
            
        });
        
    } catch (\Exception $e) {
        Log::error('Error al generar factura', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Error al generar factura: ' . $e->getMessage(),
        ], 500);
    }
}
}
