<?php
namespace App\Exceptions\Facturama;

class FacturamaException extends \Exception
{
    public function __construct(
        public readonly int $statusCode,
        public readonly ?string $rawBody,
        public readonly array $validationErrors,  // ['Mensaje del SAT', ...]
        public readonly string $reference,
        string $message,
    ) {
        parent::__construct($message);
    }

    /** Lo único que ve el cliente. */
    public function userMessage(): string
    {
        if ($this->validationErrors !== []) {
            return implode(' ', $this->validationErrors);
        }

        return "No pudimos generar tu factura en este momento. "
             . "Intenta de nuevo o contáctanos a soporte@pcbtroniks.com con la referencia {$this->reference}.";
    }
}