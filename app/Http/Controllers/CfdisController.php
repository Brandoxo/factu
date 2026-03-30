<?php

namespace App\Http\Controllers;

use App\Http\Requests\CfdiFormRequest;
use App\Services\FacturamaFilesService;
use Illuminate\Support\Facades\Log;

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
    try {
        $response = $this->facturamaFilesService->generateInvoice($request->validated());

        return response()->json(
            $response,
            $response['status_code'] ?? (($response['success'] ?? false) ? 200 : 500)
        );
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
