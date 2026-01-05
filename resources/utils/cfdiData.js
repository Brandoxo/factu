import { getTotalRate } from "./helpers.js";

export const createCfdiData = (form, items, subtotal) => {
  // Calcular el total de ISH basado en los items
  console.log("Calculando ISH para los items:", items);
  const Ish = 0.04; // Tasa del ISH (4%)
  const totalIsh = Number((subtotal * Ish).toFixed(2));


  return {
    Serie: "H", //Serie que identifica el tipo de comprobante, en este caso "H" para hotelería
    Currency: "MXN", //Moneda en la que se emite el comprobante
    ExpeditionPlace: "44520", //Código postal del lugar de expedición
    PaymentConditions: "", //Condiciones de pago, puede estar vacío si no aplica
    Folio: "100", //Probablemente se deba generar dinámicamente en la base de datos
    CfdiType: "I", //Tipo de comprobante, "I" para ingresos o "E" para egresos
    PaymentForm: "03", //Código que indica la forma de pago, 01 para "Efectivo", 02 para "Cheque", 03 para "Transferencia electrónica", y 04 para "Tarjeta de crédito", etc
    PaymentMethod: "PUE", //Método de pago, "PUE" para "Pago en una sola exhibición" o "PPD" para "Pago en parcialidades o diferido"

    //Datos del receptor (cliente)
    Receiver: {
      Rfc: form.rfc.toUpperCase(),
      Name: form.razonSocial.toUpperCase(),
      Email: form.email,
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
            TotalDeRetenciones: 0.0,
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
