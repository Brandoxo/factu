export const additionalItems = (reservation, startIndex) => {
  // Obtener el total de items adicionales (puede ser un número o un array)
  let additionalItemsValue = reservation.balanceDetailed.additionalItems;
  console.log("Generando item adicional con valor desde AdditionaItems:", additionalItemsValue);

  const subTotalExtra = Number(additionalItemsValue / 1.16).toFixed(2);
  console.log("Valor de cargos adicionales ajustado (sin IVA):", subTotalExtra);

  let ivaExtra = (subTotalExtra * 0.16).toFixed(2);
  console.log("IVA calculado para cargos adicionales:", ivaExtra);
  
  let totalExtra = Number(additionalItemsValue).toFixed(2);
  console.log("Total de cargos adicionales calculado:", totalExtra);
  
  return {
    ProductCode: "90111500", //Código estándar para servicios de alojamiento
    IdentificationNumber: `${String(startIndex + 1).padStart(3, "0")}-EXTRAS`, //Número de identificación único
    Description: `CARGOS ADICIONALES / SERVICIOS EXTRAS`, //Descripción del servicio
    Unit: "NO APLICA", //Unidad de medida, "NO APLICA" para servicios
    UnitCode: "E48", //Código de unidad de medida estándar para servicios
    UnitPrice: subTotalExtra, //Precio unitario del servicio
    Quantity: 1, //Cantidad de servicios
    Subtotal: subTotalExtra, //Subtotal antes de impuestos
    Discount: 0.0, //Descuento aplicado, si es que hay alguno
    TaxObject: "02", //Objeto del impuesto, "02" para servicios gravados
          // Impuestos aplicables: solo incluir cuando el Objeto de Impuesto sea "02"
        Taxes: [
        {
          Total: ivaExtra, //Total del impuesto
          Name: "IVA", //Nombre del impuesto
          Base: subTotalExtra, //Base sobre la cual se calcula el impuesto
          Rate: 0.16, //Tasa del impuesto (16% en este caso)
          IsRetention: false, //Indica si es una retención o un traslado
        }
        ],
    Total: totalExtra, //Total después de impuestos
  }; 
};