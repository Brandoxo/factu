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
    
    public function store($cfdiData, $cfdiResponse, $storageResponse)
    {
       
        try {
 
            $storeResponse = $this->facturamaFilesService->storeCfdiData($cfdiData, $cfdiResponse, $storageResponse);
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
    try {
        // Obtener CFDI desde Facturama API
        $cfdiResponse = $this->facturamaFilesService->fetchCfdiFromApi($cfdiData, $request);
        
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
        
        //usar ruta storeCfdiData para guardar en BD;
        $this->store($cfdiData, $cfdiResponse, $storageData);
        
        $allResponse = [
            'cfdi' => $cfdiResponse->json(),
            'storage' => $storageData
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
