<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e0e0e0; }
        .header { background-color: #1a237e; color: #ffffff; padding: 30px; text-align: center; }
        .header h2 { margin: 0; font-size: 24px; letter-spacing: 1px; }
        .content { padding: 30px; }
        .invoice-details { background-color: #f8f9fa; border-radius: 6px; padding: 20px; margin: 20px 0; border-left: 4px solid #1a237e; }
        .detail-row { margin-bottom: 10px; display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .label { font-weight: bold; color: #555; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #777; background-color: #f4f4f4; }
        .btn-container { text-align: center; margin-top: 25px; }
        .btn { display: inline-block; padding: 12px 20px; margin: 5px; color: #ffffff !important; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 14px; }
        .btn-pdf { background-color: #d32f2f; }
        .btn-xml { background-color: #455a64; }
        .total-row { font-size: 18px; font-weight: bold; color: #1a237e; border: none; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Comprobante Fiscal</h2>
        </div>
        
        <div class="content">
            <p>Estimado(a) <strong>{{ $data['cfdiResponse']['Receiver']['Name'] }}</strong>,</p>
            <p>Le informamos que su factura ha sido generada exitosamente. A continuación, le presentamos el resumen de su consumo:</p>
            
            <div class="invoice-details">
                <div class="detail-row">
                    <span class="label">Folio de Factura:</span>
                    <span>{{ $data['cfdiResponse']['Id'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">RFC Receptor:</span>
                    <span>{{ $data['cfdiResponse']['Receiver']['Rfc'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Subtotal:</span>
                    <span>${{ number_format($data['cfdiResponse']['Subtotal'], 2) }}</span>
                </div>
                <div class="total-row">
                    <span>Total a Pagar:</span>
                    <span>${{ number_format($data['cfdiResponse']['Total'], 2) }}</span>
                </div>
            </div>

            <p style="font-size: 14px; color: #666;">Puede descargar sus archivos fiscales utilizando los siguientes botones:</p>

            <div class="btn-container">
                <a href="{{ $data['storageResponse']['files']['pdf'] }}" class="btn btn-pdf">Descargar PDF</a>
                <a href="{{ $data['storageResponse']['files']['xml'] }}" class="btn btn-xml">Descargar XML</a>
            </div>
        </div>

        <div class="footer">
            <p><strong>Hotel Ronda Minerva</strong><br>
            Este es un correo automático, por favor no responda a este mensaje.</p>
        </div>
    </div>
</body>
</html>