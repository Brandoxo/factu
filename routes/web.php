<?php

use App\Http\Controllers\CfdisController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontController;

Route::get('/', [FrontController::class, 'index'])->name('home');

Route::post('/restaurant/submit-form', [FrontController::class, 'submitRestaurantForm'])->name('restaurant.submit.form');

Route::post('/hotel/submit-form', [FrontController::class, 'submitHotelForm'])->name('hotel.submit.form');

Route::get('/billing/{reservationID}', [FrontController::class, 'showBillingForm'])->name('billing.form');

Route::post('/billing/generate-invoice', [CfdisController::class, 'generateInvoice'])->name('billing.generate.invoice');