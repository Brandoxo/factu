import { getTotalRate } from "./helpers";
import { calculateIsh } from "./helpers.js";
import { additionalItems } from "./additionalItems.js";

export const items = (reservation) => {
  // Generar items de habitaciones
  const roomItems = reservation.assigned.map((room, index) => {
    const roomSubtotal = getTotalRate(room.dailyRates);
    console.log('Subtotal de la habitación calculado:', roomSubtotal);

    const ish = Number(calculateIsh(roomSubtotal, reservation.startDate.slice(0,4)).toFixed(2));
    console.log('ISH calculado para la habitación:', ish);
    const iva = Number((roomSubtotal * 0.16).toFixed(2));
    console.log('Iva calculado para la habitación:', iva);
    const roomTotal = Number((roomSubtotal + iva + ish).toFixed(2));
    console.log('Total de la habitación calculado:', roomTotal);
    
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
      TaxObject: "02", //Objeto del impuesto, "02" para servicios gravados

      // Impuestos aplicables
      Taxes: [
        {
          Total: iva, //Total del impuesto
          Name: "IVA", //Nombre del impuesto
          Base: roomSubtotal, //Base sobre la cual se calcula el impuesto
          Rate: 0.16, //Tasa del impuesto (16% en este caso)
          IsRetention: false, //Indica si es una retención o un traslado
        },
        {
          Total: ish, //Total del ISH (0 si no aplica)
          Name: "ISH", //Nombre del impuesto local
          Base: roomSubtotal, //Base sobre la cual se calcula el ISH
          Rate: 0.04, //Tasa del ISH (4%)
          IsRetention: false, //Indica si es una retención o un traslado
        }
      ],
      //Total después de impuestos
      Total: roomTotal,
    };
  });

  // Si hay items adicionales, agregarlos al array
  const additionalItemsValue = reservation.balanceDetailed?.additionalItems;
  
  if (additionalItemsValue && additionalItemsValue > 0) {
    roomItems.push(additionalItems(reservation, roomItems.length));
    console.log("Items adicionales agregados:", roomItems);
  } else {
    console.log("No hay items adicionales para agregar.", roomItems);
  }

  return roomItems;
};
