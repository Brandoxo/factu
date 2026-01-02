export const items = (item) => {
  return [
    {
      ProductCode: "10101504", //Código estándar para servicios de alojamiento
      IdentificationNumber: "001",
      Description: "SERVICIO DE ALOJAMIENTO", //Descripción del servicio
      Unit: "NO APLICA", //Unidad de medida, "NO APLICA" para servicios
      UnitCode: "E49", //Código de unidad de medida estándar para servicios
      UnitPrice: 100.0, //Precio unitario del servicio
      Quantity: item.assigned.length, //Cantidad de noches o servicios
      Subtotal: item.balanceDetailed.subTotal, //Subtotal antes de impuestos
      Discount: 0.0, //Descuento aplicado, si es que hay alguno

      // Impuestos aplicables
      Taxes: [
        {
          Total: 240.0, //Total del impuesto
          Name: "IVA", //Nombre del impuesto
          Base: 1500.0, //Base sobre la cual se calcula el impuesto
          Rate: 0.16, //Tasa del impuesto (16% en este caso)
          IsRetention: false, //Indica si es una retención o un traslado
        },
      ],
      //Total después de impuestos
      Total: item.balanceDetailed.grandTotal,
    },
  ];
};
