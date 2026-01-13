import { getTotalRate } from "./helpers";
import { calculateIsh } from "./helpers.js";
import { additionalItems } from "./additionalItems.js";

export const items = (reservation) => {
  // Generar items de habitaciones
  const roomItems = reservation.assigned.map((room, index) => {
    let roomSubtotal = getTotalRate(room.dailyRates);
    console.log('Subtotal de la habitación calculado:', roomSubtotal);
      const taxesIncluded = reservation.balanceDetailed.taxesFees;
      const isTaxable = taxesIncluded === 0 ? true : false;
      
      let ish = 0;
      let iva = 0;
      let roomTotal = 0;
      let totalBase = 0;
      
      if (!isTaxable) {
        // Los impuestos NO están incluidos: calcular sobre el subtotal
        ish = Number(calculateIsh(roomSubtotal, reservation.startDate.slice(0,4)).toFixed(2));
        iva = Number((roomSubtotal * 0.16).toFixed(2));
        roomTotal = Number((roomSubtotal + iva + ish).toFixed(2));
        console.log('Impuestos NO incluidos - Subtotal:', roomSubtotal, 'IVA:', iva, 'ISH:', ish, 'Total:', roomTotal);
      } else {
        // Los impuestos SÍ están incluidos: extraer el subtotal base
        roomTotal = roomSubtotal; // El total ya incluye los impuestos
        roomSubtotal = Number((roomTotal / 1.21).toFixed(2)); // Dividir entre 1.21 (1 + 0.16 + 0.04)
        iva = Number((roomSubtotal * 0.16).toFixed(2));
        ish = Number((roomTotal - roomSubtotal - iva).toFixed(2)); // Ajustar ISH para que cuadre exacto
        console.log('Impuestos incluidos - Total:', roomTotal, 'Subtotal base:', roomSubtotal, 'IVA:', iva, 'ISH:', ish);
      }
    
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

      // Impuestos aplicables: solo incluir cuando el Objeto de Impuesto sea "02"
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
