<?php

namespace App\Services\Facturama;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use Exception;

class FacturamaService
{
    private string $apiUrl;
    private string $user;
    private string $password;

    public function __construct()
    {
        $this->apiUrl = config('services.facturama.url', 'https://apisandbox.facturama.mx'); 
        $this->user = config('services.facturama.user');
        $this->password = config('services.facturama.password');
    }

    /**
     * Envía el payload a timbrar.
     * @throws Exception Si el PAC o el SAT rechazan el documento.
     */
    public function stampInvoice(array $payload): array
    {
        $response = Http::withBasicAuth($this->user, $this->password)
            ->timeout(30) // Evitamos que la petición quede colgada indefinidamente si el SAT o Facturama están teniendo problemas.
            ->post("{$this->apiUrl}/3/cfdis", $payload);

        return $this->handleResponse($response);
    }

    /**
     * Centralizamos el manejo de errores del API.
     */
    private function handleResponse(Response $response): array
    {
        if ($response->successful()) {
            return $response->json();
        }

        // 1. Extraemos toda la información de la petición fallida
        $statusCode = $response->status();
        $rawBody = $response->body();
        $headers = $response->headers();

        // 2. Lo guardamos todo en el archivo laravel.log
        Log::error('FACTURAMA API ERROR CRÍTICO', [
            'HTTP_STATUS' => $statusCode,
            'HEADERS' => $headers,
            'RAW_BODY' => $rawBody
        ]);

        // 3. Armamos un mensaje de rescate para el frontend
        // Así, si el cuerpo está vacío, al menos el modal dirá "Error HTTP 401", en lugar de salir en blanco.
        $mensajeFrontend = "Error HTTP {$statusCode} devuelto por Facturama.";
        if (!empty($rawBody)) {
            $mensajeFrontend .= " Detalles: " . $rawBody;
        }

        throw new \Exception($mensajeFrontend);
    }

    /**
     * Descargar el archivo de la factura desde Facturama.
     * @param string $facturamaId ID interno de Facturama (No el UUID del SAT).
     * @param string $format 'pdf' o 'xml'.
     * @return string El contenido binario del archivo.
     */
    public function downloadInvoiceFile(string $facturamaId, string $format = 'pdf'): string
    {
        // El tipo 'issued' indica que es una factura de ingreso emitida por ti.
        $response = Http::withBasicAuth($this->user, $this->password)
            ->timeout(30)
            ->get("{$this->apiUrl}/cfdi/{$format}/issued/{$facturamaId}"); //

        $data = $this->handleResponse($response);

        // Facturama devuelve el archivo codificado en Base64 en el nodo 'Content'.
        // Lo decodificamos a binario inmediatamente para no lidiar con Base64 en el resto de la app.
        return base64_decode($data['Content']);
    }
}