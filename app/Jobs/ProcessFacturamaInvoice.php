<?php

namespace App\Jobs;

use App\Models\PcbresInvoice; // Asegúrate de tener este modelo creado
use App\Services\Facturama\FacturamaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Exception;

class ProcessFacturamaInvoice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public PcbresInvoice $invoice;

    // Resiliencia: Si Facturama falla por un error 500, reintentamos 3 veces.
    public $tries = 3;
    
    // Backoff exponencial: Espera 30s, luego 60s, luego 120s antes de reintentar.
    public $backoff = [30, 60, 120];

    public function __construct(PcbresInvoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Inyectamos nuestro Service directamente en el método handle.
     * Laravel se encarga de instanciarlo por nosotros.
     */
    public function handle(FacturamaService $service): void
    {
        // 1. Cláusula de Guardia (Idempotencia)
        // Si por algún milagro de la red este Job se ejecuta dos veces, evitamos timbrar doble.
        if ($this->invoice->status === 'stamped') {
            return;
        }

        try {
            // Actualizamos la máquina de estados
            $this->invoice->update(['status' => 'processing']);

            // 2. Extraemos el snapshot inmutable que el Controlador guardó previamente
            // Nota: Nos aseguramos que el cast del modelo lo devuelva como array
            $payload = $this->invoice->stamped_fiscal_data;

            // 3. Timbrar en el SAT
            $response = $service->stampInvoice($payload);
            
            $uuid = $response['Complement']['TaxStamp']['Uuid'];
            $facturamaId = $response['Id'];

            // 4. Descargar Documentos
            $pdfContent = $service->downloadInvoiceFile($facturamaId, 'pdf');
            $xmlContent = $service->downloadInvoiceFile($facturamaId, 'xml');

            // 5. Guardar en el disco
            $pdfPath = "invoices/{$uuid}.pdf";
            $xmlPath = "invoices/{$uuid}.xml";
            
            Storage::disk('local')->put($pdfPath, $pdfContent);
            Storage::disk('local')->put($xmlPath, $xmlContent);

            // 6. Sellar el Libro Mayor (Éxito)
            $this->invoice->update([
                'status' => 'stamped',
                'uuid' => $uuid,
                'facturama_id' => $facturamaId,
                'pdf_path' => $pdfPath,
                'xml_path' => $xmlPath,
                'error_log' => null, // Limpiamos cualquier error de intentos previos
            ]);

        } catch (Exception $e) {
            // 7. Registro de la tragedia
            $this->invoice->update([
                'status' => 'failed',
                'error_log' => $e->getMessage(),
            ]);

            // IMPORTANTE: Volvemos a lanzar la excepción. 
            // Si no lo hacemos, Laravel pensará que el Job terminó con éxito y no aplicará los reintentos ($tries).
            throw $e;
        }
    }
}