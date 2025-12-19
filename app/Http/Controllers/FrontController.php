<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class FrontController extends Controller
{
    public function index()
    {
        return Inertia::render('Index/Index');
    }

    public function submitRestaurantForm(Request $request)
    {
        $data = $request->all();

        return response()->json([
            'message' => 'Restaurant form submitted successfully!',
            'data' => $data,
        ]);
    }

    public function submitHotelForm(Request $request)
    {
        $data = $request->all();

        return response()->json([
            'message' => 'Hotel form submitted successfully!',
            'data' => $data,
        ]);
    }
}