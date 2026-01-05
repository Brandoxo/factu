import { getTotalRate } from "./helpers";

export const items = (reservation) => {
  return reservation.assigned.map((room, index) => {
    const roomSubtotal = getTotalRate(room.dailyRates);
    const iva = Number((roomSubtotal * 0.16).toFixed(2));
    const roomTotal = Number((roomSubtotal + iva).toFixed(2));
    return {
      ProductCode: "10101504", //Código estándar para servicios de alojamiento
      IdentificationNumber: `${String(index + 1).padStart(3, "0")}-${
        room.roomName
      }`, //Número de identificación único para la habitación
      Description: `SERVICIO DE ALOJAMIENTO - HAB ${room.roomName} - ${room.roomTypeName}`, //Descripción del servicio con detalles de la habitación
      Unit: "NO APLICA", //Unidad de medida, "NO APLICA" para servicios
      UnitCode: "E49", //Código de unidad de medida estándar para servicios
      UnitPrice: roomSubtotal, //Precio unitario del servicio
      Quantity: 1, //Cantidad de noches o servicios, generalmente 1 por Item
      Subtotal: roomSubtotal, //Subtotal antes de impuestos
      Discount: 0.0, //Descuento aplicado, si es que hay alguno

      // Impuestos aplicables
      Taxes: [
        {
          Total: iva, //Total del impuesto
          Name: "IVA", //Nombre del impuesto
          Base: roomSubtotal, //Base sobre la cual se calcula el impuesto
          Rate: 0.16, //Tasa del impuesto (16% en este caso)
          IsRetention: false, //Indica si es una retención o un traslado
        },
      ],
      //Total después de impuestos
      Total: roomTotal,
    };
  });
};
