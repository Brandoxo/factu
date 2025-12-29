export const createCfdiDataH = (form, reservation) => {
return {
    "Serie": 'H',
    "Currency": "MXN",
    "ExpeditionPlace": "44520",
    "PaymentConditions": "",
    "Folio": "100",
    "CfdiType": "I",
    "PaymentForm": "",
    "PaymentMethod": "PUE",
    "Receiver": {
        "Rfc": form.rfc,
        "Name": form.razonSocial,
        "CfdiUse": form.usoCfdi,
        "FiscalRegime": form.regimenFiscal,
        "TaxZipCode": form.codigoPostal,
    },
    "Items": [
        {
        "ProductCode": "10101504",
        "IdentificationNumber": "EDL",
        "Description": reservation.assigned[0].roomType,
        "Unit": "NO APLICA",
        "UnitCode": "MTS",
        "UnitPrice": reservation.balanceDetailed.subTotal,
        "Quantity": 1.0,
        "Subtotal":  reservation.balanceDetailed.subTotal,
        "Taxes": [
            {
            "Total": 16.0,
            "Name": "IVA",
            "Base": 100.0,
            "Rate": 0.16,
            "IsRetention": false
            }
        ],
        "Total": 116.0
        },
        {
        "ProductCode": "10101504",
        "IdentificationNumber": "001",
        "Description": "SERVICIO DE ALOJAMIENTO",
        "Unit": "NO APLICA",
        "UnitCode": "E49",
        "UnitPrice": 100.0,
        "Quantity": 15.0,
        "Subtotal": reservation.balanceDetailed.subTotal,
        "Discount": 0.0,
        "Taxes": [
            {
            "Total": 240.0,
            "Name": "IVA",
            "Base": 1500.0,
            "Rate": 0.16,
            "IsRetention": false
            }
        ],
        "Total": reservation.balanceDetailed.grandTotal
        }
  ]};
};