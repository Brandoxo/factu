<?php

namespace App\Resources\Pcbrestaurant;

use App\Resources\Pcbrestaurant\GetOrders;
use Illuminate\Support\Facades\URL;

class PcbrestaurantResource {

    private GetOrders $getOrders;

    public function __construct() {
        $this->getOrders = new GetOrders();
    }

    private function buildSignedBillingUrl($ticketFolio, $totalAmount, $date) {
        $signedUrl = URL::temporarySignedRoute('pcbrestaurant.billing',
                    now()->addMinutes(30),
                    [
                        'ticketFolio' => $ticketFolio,
                        'totalAmount' => $totalAmount,
                        'date' => $date
                    ]
                    );
        return $signedUrl;
    }

    public function getBillingUrlForOrder($ticketFolio, $totalAmount, $date) {
        $orderResponse = $this->getOrders->getOrderById($ticketFolio);
        if (!$orderResponse) {
            return null;
        }
        // Aquí podrías agregar lógica adicional para validar el pedido o calcular el total si es necesario
        return $this->buildSignedBillingUrl($ticketFolio, $totalAmount, $date);
    }

    public function getOrderDetails($ticketFolio) {
        $orderResponse = $this->getOrders->getOrderById($ticketFolio);
        if (!$orderResponse) {
            return null;
        }
        return $orderResponse['order'];
    }
}