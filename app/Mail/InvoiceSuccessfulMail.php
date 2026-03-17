<?php

namespace App\Mail;

use App\Models\PcbresInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceSuccessfulMail extends Mailable
{
    use Queueable, SerializesModels;

    public PcbresInvoice $invoice;

    /**
     * Create a new message instance.
     */
    public function __construct(PcbresInvoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu Factura de Consumo está lista - Ticket #' . $this->invoice->pos_order_id,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            // Esta es la vista que Laravel buscará para el cuerpo del correo
            view: 'emails.invoice_success', 
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        // Aquí es donde ocurre la magia: leemos directamente desde el Storage local
        return [
            Attachment::fromStorageDisk('local', $this->invoice->pdf_path)
                ->as('Factura_' . $this->invoice->uuid . '.pdf')
                ->withMime('application/pdf'),
                
            Attachment::fromStorageDisk('local', $this->invoice->xml_path)
                ->as('Factura_' . $this->invoice->uuid . '.xml')
                ->withMime('application/xml'),
        ];
    }
}