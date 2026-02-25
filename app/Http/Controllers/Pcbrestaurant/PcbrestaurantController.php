<?php

namespace App\Http\Controllers\Pcbrestaurant;

use App\Resources\Pcbrestaurant\GetOrders;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PcbrestaurantController extends Controller
{
    public function index() {
        $response = new GetOrders();
        $orders = $response->getAllOrders();

        // Build Order Datails Data for Cfdi Generation
        $payloadBuilder = new \App\Services\Billing\FacturamaPayloadBuilder();
        $customerFiscalData = [
            'Rfc' => '',
            'Name' => '',
            'email' => '',
            'TaxZipCode' => '',
            'FiscalRegime' => '',
            'CfdiUse' => '',
            'paymentMethod' => '',
            ];
        $payload = $payloadBuilder->buildFromPosOrder($orders['order'][0], $customerFiscalData);
        return response()->json(['message' => 'Pcbrestaurant API is working correctly', 'orders' => $orders, 'payload' => $payload]);
    }

    public function show($id) {
        $response = GetOrders::getOrderById($id);
        return $response['order'];
        return response()->json(['message' => "Showing order with ID: $id", 'order' => $response]);
    }
}
