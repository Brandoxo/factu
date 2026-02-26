<?php

namespace App\Http\Controllers\Pcbrestaurant;

use App\Http\Requests\Pcbrestaurant\ValidateSearchOrderRequest;
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

    public function billing($ticketFolio = null) {
        $orderData = null;
        if ($ticketFolio) {
            $response = GetOrders::getOrderById($ticketFolio);
            if ($response) {
                $orderData = $response['order'];
            } else {
                return response()->json(['message' => "Orden no encontrada: $ticketFolio"], 404);
            }
        }
        return inertia('Billing/PcbresBillingForm', ['ticketFolio' => $ticketFolio, 'orderData' => $orderData]);
    }

    public function show(ValidateSearchOrderRequest $request, $ticketFolio) {
        $response = GetOrders::getOrderById($ticketFolio);
        if (!$response) {
            return response()->json(['message' => "Orden no encontrada: $ticketFolio"], 404);
        }
        return $response['order'];
        return response()->json(['error' => "Showing order with ticket folio: $ticketFolio", 'order' => $response]);
    }

    public function apiShow(ValidateSearchOrderRequest $request, $id) {
        $response = GetOrders::getOrderById($id);
        if (!$response) {
            return response()->json(['error' => "Orden no encontrada: $id"], 404);
        }
        return response()->json(['message' => "Showing order with ID: $id", 'order' => $response]);
    }
}
