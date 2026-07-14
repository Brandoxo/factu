<?php

namespace App\Services\Billing;

class FacturamaPayloadBuilder
{
    private const TAX_RATE = 1.16;
    private const CLAVE_PROD_SERV_RESTAURANTE = '90101501';
    private const CLAVE_UNIDAD_SERVICIO = 'E48';

    public function buildFromPosOrder(array $orderData, array $customerFiscalData): array
    {
        $items = collect($orderData['orderDetails'])->map(function ($detail) {
            return $this->buildConcept($detail);
        })->toArray();

        return [
            'Receiver' => $customerFiscalData, // Tus datos de prueba van aquí
            'CfdiType' => 'I', // Ingreso
            'PaymentForm' => '01', // Efectivo (Debería venir de tu POS idealmente)
            'PaymentMethod' => 'PUE', // Pago en una sola exhibición
            'ExpeditionPlace' => '44100', // Tu CP de expedición
            'Items' => $items,
        ];
    }

    private function buildConcept(array $detail): array
    {
        $totalIncludingTax = (float) $detail['subtotal'];
        $quantity = (int) $detail['quantity'];
        
        // El precio unitario real con impuestos incluidos
        $unitPriceWithTax = $totalIncludingTax / $quantity;
        
        // Desglose matemático seguro
        $basePrice = round($unitPriceWithTax / self::TAX_RATE, 2);
        $taxAmount = round($unitPriceWithTax - $basePrice, 2); 

        return [
            'Quantity' => $quantity,
            'ProductCode' => self::CLAVE_PROD_SERV_RESTAURANTE,
            'UnitCode' => self::CLAVE_UNIDAD_SERVICIO,
            'Unit' => 'Servicio',
            'Description' => $detail['product']['name'],
            'UnitPrice' => $basePrice,
            'Subtotal' => $basePrice * $quantity,
            'Taxes' => [
                [
                    'Total' => $taxAmount * $quantity,
                    'Name' => 'IVA',
                    'Base' => $basePrice * $quantity,
                    'Rate' => 0.16,
                    'IsRetention' => false,
                ]
            ],
        ];
    }
}