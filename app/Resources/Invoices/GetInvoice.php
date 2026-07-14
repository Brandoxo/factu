<?php 

namespace App\Resources\Invoices;

use App\Models\PcbresInvoice;

class GetInvoice {

    public function getInvoiceById(int $invoiceId) {
        return PcbresInvoice::where('pos_order_id', $invoiceId)->first();
    }

    public function GetInvoiceIfExists(int $invoiceId) {
        return $this->getInvoiceById($invoiceId);
    }
}