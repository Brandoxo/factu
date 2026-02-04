<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            line-height: 1.6; 
            color: #333; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        .container { 
            max-width: 650px; 
            margin: 0 auto; 
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px; 
            overflow: hidden; 
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        .header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff; 
            padding: 40px 30px; 
            text-align: center; 
        }
        .header h1 { 
            margin: 0; 
            font-size: 28px; 
            font-weight: 600;
            letter-spacing: 0.5px; 
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }
        .header p {
            margin-top: 8px;
            font-size: 14px;
            opacity: 0.9;
        }
        .content { 
            padding: 40px 30px; 
            background-color: #ffffff;
        }
        .greeting {
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
        }
        .greeting strong {
            color: #667eea;
        }
        .intro-text {
            font-size: 15px;
            color: #666;
            margin-bottom: 25px;
            line-height: 1.7;
        }
        .invoice-details { 
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            backdrop-filter: blur(10px);
            border-radius: 12px; 
            padding: 25px; 
            margin: 25px 0; 
            border: 2px solid rgba(102, 126, 234, 0.2);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.1);
        }
        .detail-row { 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid rgba(102, 126, 234, 0.15);
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .label { 
            font-weight: 600; 
            color: #555;
            font-size: 15px;
        }
        .value {
            font-weight: 500;
            color: #333;
            font-size: 15px;
        }
        .total-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            padding: 20px 25px;
            margin: 25px 0;
            text-align: center;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }
        .total-label {
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 5px;
        }
        .total-amount {
            color: #ffffff;
            font-size: 32px;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }
        .download-section {
            text-align: center;
            margin: 30px 0 20px 0;
        }
        .download-title {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .btn-container { 
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        .btn { 
            display: inline-block; 
            padding: 14px 30px; 
            color: #ffffff !important; 
            text-decoration: none; 
            border-radius: 50px; 
            font-weight: 600; 
            font-size: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        .btn-pdf { 
            background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
        }
        .btn-pdf:hover {
            box-shadow: 0 6px 20px rgba(244, 67, 54, 0.4);
            transform: translateY(-2px);
        }
        .btn-xml { 
            background: linear-gradient(135deg, #607d8b 0%, #455a64 100%);
        }
        .btn-xml:hover {
            box-shadow: 0 6px 20px rgba(96, 125, 139, 0.4);
            transform: translateY(-2px);
        }
        .footer { 
            text-align: center; 
            padding: 25px; 
            font-size: 13px; 
            color: #666; 
            background: linear-gradient(to bottom, #f9f9f9 0%, #f0f0f0 100%);
            border-top: 1px solid #e0e0e0;
        }
        .footer strong {
            color: #667eea;
            font-size: 15px;
        }
        .divider {
            height: 2px;
            background: linear-gradient(to right, transparent, rgba(102, 126, 234, 0.5), transparent);
            margin: 20px 0;
        }
        @media only screen and (max-width: 600px) {
            .container { margin: 10px; }
            .header { padding: 30px 20px; }
            .header h1 { font-size: 24px; }
            .content { padding: 25px 20px; }
            .btn-container { flex-direction: column; }
            .btn { width: 100%; }
            .total-amount { font-size: 28px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✓ Factura Generada Exitosamente</h1>
            <p>Comprobante Fiscal Digital por Internet</p>
        </div>

        <!-- Información del Emisor -->
        <div style="text-align: center; padding: 25px 30px; background: #f9f9f9; border-bottom: 2px dashed #e0e0e0;">
            <h2 style="font-size: 20px; font-weight: 700; color: #333; margin: 0 0 8px 0; text-transform: uppercase; letter-spacing: 0.5px;">
                {{ $data['cfdiResponse']['Issuer']['TaxName'] }}
            </h2>
            <p style="font-size: 13px; color: #666; margin: 3px 0;">{{ $data['cfdiResponse']['Issuer']['TaxAddress']['Street'] }}</p>
            <p style="font-size: 13px; color: #666; margin: 3px 0;">{{ $data['cfdiResponse']['Issuer']['TaxAddress']['Neighborhood'] }}</p>
            <p style="font-size: 13px; color: #666; margin: 3px 0;">{{ $data['cfdiResponse']['Issuer']['TaxAddress']['State'] }}</p>
            <p style="font-size: 13px; color: #666; margin: 3px 0;">CP: {{ $data['cfdiResponse']['Issuer']['TaxAddress']['ZipCode'] }}</p>
        </div>
        
        <div class="content">
            <p class="greeting">Estimado(a) <strong>{{ $data['cfdiResponse']['Receiver']['Name'] }}</strong>,</p>
            <p class="intro-text">
                Nos complace informarle que su factura ha sido generada y timbrada correctamente. 
                A continuación encontrará los detalles de su comprobante fiscal:
            </p>
            
            <div class="invoice-details">
                <div class="detail-row">
                    <span class="label">Folio: </span>
                    <span class="value">{{ $data['cfdiResponse']['Folio'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">ID de Factura: </span>
                    <span class="value">{{ $data['cfdiResponse']['Id'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Fecha: </span>
                    <span class="value">{{ $data['cfdiResponse']['Date'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">RFC Receptor: </span>
                    <span class="value">{{ $data['cfdiResponse']['Receiver']['Rfc'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Razón Social: </span>
                    <span class="value">{{ $data['cfdiResponse']['Receiver']['Name'] }}</span>
                </div>
            </div>

            <!-- Items de la factura -->
            @if(isset($data['cfdiResponse']['Items']) && count($data['cfdiResponse']['Items']) > 0)
            <div style="margin: 25px 0;">
                <h3 style="font-size: 16px; font-weight: 600; color: #667eea; margin-bottom: 15px;">Conceptos Facturados</h3>
                <div style="background: #f9f9f9; border-radius: 8px; padding: 15px;">
                    @foreach($data['cfdiResponse']['Items'] as $item)
                    <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee;">
                        <div style="flex: 1;">
                            <div style="font-weight: 500; color: #333; font-size: 14px;">{{ $item['Description'] }}</div>
                            <div style="font-size: 12px; color: #999; margin-top: 3px;">Cantidad: {{ $item['Quantity'] }}</div>
                        </div>
                        <div style="font-weight: 600; color: #667eea; font-size: 14px;">
                            ${{ number_format($item['UnitValue'] * $item['Quantity'], 2) }} MXN
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Desglose de impuestos -->
            <div style="background: #f9f9f9; border-radius: 8px; padding: 20px; margin: 20px 0;">
                <div style="display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; color: #666;">
                    <span>Subtotal:</span>
                    <span style="font-weight: 600;">${{ number_format($data['cfdiResponse']['Subtotal'], 2) }} MXN</span>
                </div>
                @if(isset($data['cfdiResponse']['Taxes']) && count($data['cfdiResponse']['Taxes']) > 0)
                    @foreach($data['cfdiResponse']['Taxes'] as $tax)
                    <div style="display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; color: #666;">
                        <span>{{ $tax['Name'] ?? 'IVA' }} ({{ isset($tax['Rate']) ? '16'. '%' : '16%' }}):</span>
                        <span style="font-weight: 600;">${{ number_format($tax['Total'], 2) }} MXN</span>
                    </div>
                    @endforeach
                @endif
            </div>

            <div class="total-section">
                <div class="total-label">Total a Pagar</div>
                <div class="total-amount">${{ number_format($data['cfdiResponse']['Total'], 2) }} MXN</div>
            </div>

            <div class="divider"></div>

            <div class="download-section">
                <p class="download-title">📥 Descargue sus archivos fiscales</p>
                <div class="btn-container">
                    <a href="{{ $data['storageResponse']['files']['pdf'] }}" class="btn btn-pdf">📄 Descargar PDF</a>
                    <a href="{{ $data['storageResponse']['files']['xml'] }}" class="btn btn-xml">📋 Descargar XML</a>
                </div>
            </div>
        </div>

        <div class="footer">
            <p><strong>Hotel Ronda Minerva</strong></p>
            <p style="margin-top: 8px;">Este es un correo automático, por favor no responda a este mensaje.</p>
            <p style="margin-top: 5px; font-size: 12px; color: #999;">© {{ date('Y') }} Hotel Ronda Minerva. Todos los derechos reservados.</p>
            <p style="margin-top: 5px; font-size: 12px; color: #999;">Powered by <a href="https://pcbtroniks.com" target="_blank" rel="noopener noreferrer">PCBTroniks.com</a> &copy;</p>
        </div>
    </div>
</body>
</html>