<!DOCTYPE html>
<html>

<head>
    <title>Factura Generada</title>
</head>

<body style="font-family: Arial, sans-serif; color: #333;">
    <h2>¡Hola! Tu factura ha sido procesada exitosamente.</h2>
    <p>Adjunto a este correo encontrarás los archivos PDF y XML correspondientes a tu consumo del ticket <strong>#{{ $invoice->pos_order_id }}</strong>.</p>
    <p>El folio fiscal (UUID) de tu comprobante es: <br>
        <strong>{{ $invoice->uuid }}</strong>
    </p>
    <br>
    <p>Gracias por tu preferencia.</p>
</body>

</html>