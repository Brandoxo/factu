<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FacturamaFilesService;
use App\Http\Requests\CfdiFormRequest;
use Illuminate\Http\JsonResponse;
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
        // Obtener CFDI desde Facturama API
        $cfdiResponse = $this->facturamaFilesService->fetchCfdiFromApi($cfdiData, $filteredRoomsAvailable, $optionsId, $request);
        
        // Verificar si la respuesta es un error de JsonResponse
        if ($cfdiResponse instanceof JsonResponse) {
            return $cfdiResponse;
        }
        
        if ($cfdiResponse->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener CFDI',
                'response' => $cfdiResponse->json()
            ], 500);
        }

        // Almacenar archivos del CFDI
        $storageResponse = $this->facturamaFilesService->storeCfdiFiles($cfdiResponse);
        
        // Normalizar datos de almacenamiento
        if ($storageResponse instanceof JsonResponse) {
            $data = $storageResponse->getData(true);
            if (!($data['success'] ?? true)) {
                return $storageResponse;
            }
        }

        // Normalizar datos para almacenamiento
        $storageData = $storageResponse instanceof JsonResponse
            ? $storageResponse->getData(true)
            : $storageResponse->json();

        $cfdiResponse_Array = $cfdiResponse->json();
        
        //usar ruta storeCfdiData para guardar en BD;
        if ($storageData && ($storageData['success'] ?? false))
            {

            $storeResponse = $this->store([
                'cfdiData' => $cfdiData,
                'cfdiResponse' => $cfdiResponse_Array,
                'storageData' => $storageData,
                'optionsId' => $cfdiData['optionsId'] ?? null,
                'extrasId' => $cfdiData['extrasId'] ?? null,
            ]);
            
            // Verificar si store() retornó un error (JsonResponse)
            if ($storeResponse instanceof JsonResponse) {
                return $storeResponse;
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Error en los datos de almacenamiento',
            ], 500);
        }
        
        $responseByEmail = $this->facturamaFilesService->sendFilesByEmail(
            $storeResponse['invoice_id'],
            $storeResponse['fiscal_entity_id'],
            $storeResponse['client_id'],
        );

        $allResponse = [
            'cfdiResponse' => $cfdiResponse_Array,
            'storageData' => $storageData,
            'emailSent' => $responseByEmail,
        ];
            
        return $allResponse;

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Excepción al generar factura: ' . $e->getMessage(),
        ], 500);
    }
}
}
