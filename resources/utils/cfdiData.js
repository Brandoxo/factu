export const createCfdiData = (form, items) => {
  return {
    Serie: "H", //Serie que identifica el tipo de comprobante, en este caso "H" para hotelería
    Currency: "MXN", //Moneda en la que se emite el comprobante
    ExpeditionPlace: "44520", //Código postal del lugar de expedición
    PaymentConditions: "", //Condiciones de pago, puede estar vacío si no aplica
    Folio: null,// Folio se genera automáticamente en el backend, no se envía desde frontend
    CfdiType: "I", //Tipo de comprobante, "I" para ingresos o "E" para egresos
    PaymentForm: form.paymentMethod, //Código que indica la forma de pago, 01 para "Efectivo", 02 para "Cheque", 03 para "Transferencia electrónica", y 04 para "Tarjeta de crédito", etc
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
  };
};
