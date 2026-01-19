<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class CfdisController extends Controller
{
   public function generateInvoice(Request $request)
{
    $username = env('FACTURAMA_USERAGENT');
    $password = env('FACTURAMA_PASSWORD');

    $datosCfdi = $request->cfdiData;

    $cfdiResponse = Http::withBasicAuth($username, $password)
        ->withoutVerifying()
        ->post('https://apisandbox.facturama.mx/3/cfdis', $datosCfdi);

    if (!$cfdiResponse->successful()) {
        return response()->json([
            'success' => false,
            'message' => 'Error al timbrar CFDI',
            'facturama' => $cfdiResponse->json()
        ], 500);
    }

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
        ->get("https://apisandbox.facturama.mx/xml/payroll/{$id}");

    $pdf = Http::withBasicAuth($username, $password)
        ->withoutVerifying()
        ->get("https://apisandbox.facturama.mx/pdfi/payroll/{$id}");

    if (!$xml->successful() || !$pdf->successful()) {
        return response()->json([
            'success' => false,
            'message' => 'No se pudieron descargar los archivos'
        ], 500);
    }

    \Storage::put("cfdis/{$id}.xml", $xml->body());
    \Storage::put("cfdis/{$id}.pdf", $pdf->body());

    return response()->json([
        'success' => true,
        'id' => $id,
        'files' => [
            'xml' => url("storage/cfdis/{$id}.xml"),
            'pdf' => url("storage/cfdis/{$id}.pdf"),
        ]
    ]);
 }
}
