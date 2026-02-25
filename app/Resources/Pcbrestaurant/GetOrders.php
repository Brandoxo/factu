<?php

namespace App\Resources\Pcbrestaurant;

use Illuminate\Support\Facades\Http;

class GetOrders {

    //Get All orders just from de API
    public function getAllOrders() {
        $url = config('app.pcbrestaurant.api_url') . '/orders' . '/1500';
        //dd($url);
        $response = Http::get($url);
        if ($response->successful()) {
            return $response->json();
        } else {
            // Handle error response
            return null;
        }
    }

    //Get a single order by ID just from de API
    public static function getOrderById($id) {
        $url = config('app.pcbrestaurant.api_url') . '/orders' . '/' . $id;
        $response = Http::get($url);
        if ($response->successful()) {
            return $response->json();
        } else {
            // Handle error response
            dd('Error fetching order with ID: ' . $id);
            return null;
        }
    }

}