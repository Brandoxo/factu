<?php

namespace App\Services\Facturama;

use Illuminate\Support\Facades\Http;
use Exception;
use Illuminate\Http\Client\Response;

class FacturamaService
{
    private string $apiUrl;
    private string $user;
    private string $password;

    public function __construct()
    {
        // Regla de oro: Cero credenciales hardcodeadas en el código.
        // Todo esto debe venir de tu archivo .env
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
            ->timeout(10) // Evitamos que la petición quede colgada indefinidamente si el SAT o Facturama están teniendo problemas.
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

        // Si es un 400, normalmente Facturama te dice exactamente qué nodo del XML está mal.
        if ($response->clientError()) {
            $errorData = $response->json();
            
            // Extraemos el detalle real que Facturama esconde en ModelState o Errors
            $details = $errorData['ModelState'] ?? $errorData['Errors'] ?? $errorData;
            $errorMessage = ($errorData['Message'] ?? 'Error 400') . ' | Detalles: ' . json_encode($details);
        }

        // Si es un 500, el SAT o Facturama están caídos.
        if ($response->serverError()) {
            $errorMessage = 'El proveedor de facturación está fuera de servicio temporalmente.';
        }

        // Lanzamos la excepción para que el Job de Laravel la atrape y marque el status como 'failed'
        throw new Exception("Facturama Error: " . $errorMessage);
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
            ->timeout(10)
            ->get("{$this->apiUrl}/cfdi/{$format}/issued/{$facturamaId}"); //

        $data = $this->handleResponse($response);

        // Facturama devuelve el archivo codificado en Base64 en el nodo 'Content'.
        // Lo decodificamos a binario inmediatamente para no lidiar con Base64 en el resto de la app.
        return base64_decode($data['Content']);
    }
}