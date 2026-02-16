<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FilesController extends Controller
{
    public function downloadInvoicePdf($id)
    {
        return response()->download(Storage::path("cfdis/{$id}.pdf"));
    }

    public function downloadInvoiceXML($id)
    {
        
        return response()->download(Storage::path("cfdis/{$id}.xml"));
    }
}
