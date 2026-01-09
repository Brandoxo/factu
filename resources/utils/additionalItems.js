export const additionalItems = (reservation, startIndex) => {
  // Obtener el total de items adicionales (puede ser un número o un array)
  const additionalItemsValue = reservation.balanceDetailed.additionalItems;
  console.log("Generando item adicional con valor desde AdditionaItems:", additionalItemsValue);
  
  let totalExtra = Number(additionalItemsValue);
  console.log("Total de cargos adicionales calculado:", totalExtra);
  
  return {
    ProductCode: "10101504", //Código estándar para servicios de alojamiento
    IdentificationNumber: `${String(startIndex + 1).padStart(3, "0")}-EXTRAS`, //Número de identificación único
    Description: `CARGOS ADICIONALES / SERVICIOS EXTRAS`, //Descripción del servicio
    Unit: "NO APLICA", //Unidad de medida, "NO APLICA" para servicios
    UnitCode: "E48", //Código de unidad de medida estándar para servicios
    UnitPrice: totalExtra, //Precio unitario del servicio
    Quantity: 1, //Cantidad de servicios
    Subtotal: totalExtra, //Subtotal antes de impuestos
    Discount: 0.0, //Descuento aplicado, si es que hay alguno
    TaxObject: "04", //Objeto del impuesto, "04" para servicios exentos
    Total: totalExtra, //Total después de impuestos
  }; 
};