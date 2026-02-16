<?php

use App\Http\Controllers\CfdisController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\FilesController;

Route::redirect('/', '/services');

Route::get('/services', [FrontController::class, 'index'])->name('home');

Route::post('/restaurant/submit-form', [FrontController::class, 'submitRestaurantForm'])->name('restaurant.submit.form');

Route::post('/hotel/submit-form', [FrontController::class, 'submitHotelForm'])->name('hotel.submit.form');

Route::get('/billing/{reservationID}', [FrontController::class, 'showBillingForm'])->middleware('signed')->name('billing.form');

Route::post('/billing/generate-invoice', [CfdisController::class, 'generateInvoice'])->name('billing.generate.invoice');

Route::post('/billing/store-cfdi', [CfdisController::class, 'store'])->name('billing.store.cfdi');

Route::get('/invoice-success', [FrontController::class, 'billingSuccess'])->middleware('signed')->name('billing.success');

Route::post('invoice/success/send-email', [FrontController::class, 'sendInvoiceEmail'])->name('invoice.send.email');

Route::get('/invoice/{Id}/pdf/download', [FilesController::class, 'downloadInvoicePdf'])->name('invoice.download');
Route::get('/invoice/{Id}/xml/download', [FilesController::class, 'downloadInvoiceXML'])->name('invoice.download.xml');