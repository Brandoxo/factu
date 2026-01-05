import { getTotalRate } from "./helpers.js";

export const createCfdiData = (form, items) => {
  // Calcular el total de ISH basado en los items
  let totalIsh = 0;
  items.forEach((item) => {
    // Buscar la habitación asignada correspondiente al item
    const room = item.assigned ? item.assigned[0] : null;
    if (room) {
      const roomSubtotal = getTotalRate(room.dailyRates);
      const ish = Number((roomSubtotal * 0.04).toFixed(2)); // 4% de ISH
      totalIsh += ish;
      console.log(`ISH para la habitación ${room.roomName}: ${ish}`);
    }
  });

  return {
    Serie: "H", //Serie que identifica el tipo de comprobante, en este caso "H" para hotelería
    Currency: "MXN", //Moneda en la que se emite el comprobante
    ExpeditionPlace: "44520", //Código postal del lugar de expedición
    PaymentConditions: "", //Condiciones de pago, puede estar vacío si no aplica
    Folio: "100", //Probablemente se deba generar dinámicamente en la base de datos
    CfdiType: "I", //Tipo de comprobante, "I" para ingresos o "E" para egresos
    PaymentForm: "", //Código que indica la forma de pago, 01 para "Efectivo", 02 para "Cheque", 03 para "Transferencia electrónica", y 04 para "Tarjeta de crédito", etc
    PaymentMethod: "PUE", //Método de pago, "PUE" para "Pago en una sola exhibición" o "PPD" para "Pago en parcialidades o diferido"

    //Datos del receptor (cliente)
    Receiver: {
      Rfc: form.rfc.toUpperCase(),
      Name: form.razonSocial.toUpperCase(),
      CfdiUse: form.usoCfdi,
      FiscalRegime: form.regimenFiscal,
      TaxZipCode: form.codigoPostal,
    },

    Items: items,

    // Complemento de Impuestos Locales
    Complemento: {
      Any: [
        {
          ImpuestosLocales: {
            TotalDeTraslados: totalIsh, // Suma de todos los importes de ISH
            TotalDeRetenciones: 0,
            TrasladosLocales: [
              {
                ImpLocTrasladado: "ISH",
                TasadeTraslado: 4.0, // Tasa (ej. 3.00 para 3%)
                Importe: totalIsh, // Subtotal de la habitación * 0.03
              },
            ],
          },
        },
      ],
    },
  };
};
