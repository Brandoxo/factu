<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FacturamaFilesService;
use App\Http\Requests\FilesCfdiFormRequest;

class CfdisController extends Controller
{
    protected FacturamaFilesService $facturamaFilesService;

    public function __construct(FacturamaFilesService $facturamaFilesService)
    {
        $this->facturamaFilesService = $facturamaFilesService;
    }

   public function generateInvoice(FilesCfdiFormRequest $request)
{
    $cfdiData = $request->validated();
    try {
        // Obtener CFDI desde Facturama API
        $cfdiResponse = $this->facturamaFilesService->fetchCfdiFromApi($cfdiData, $request);

        if ($cfdiResponse->failed()) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener CFDI',
                'response' => $cfdiResponse->json()
            ], 500);
        }

        // Almacenar archivos del CFDI
        $storageResponse = $this->facturamaFilesService->storeCfdiFiles($cfdiResponse);

        return $storageResponse;

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Excepción al generar factura: ' . $e->getMessage(),
        ], 500);
    }
}
}
