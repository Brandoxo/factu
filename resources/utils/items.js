import { getTotalRate } from "./helpers";
import { calculateIsh } from "./helpers.js";
import { additionalItems } from "./additionalItems.js";
import { ishWithIvaPercent } from "./helpers.js";
import { round6 } from "./helpers.js";

export const items = (reservation) => {
  // Generar items de habitaciones
  const roomItems = reservation.assigned.map((room, index) => {
    let roomSubtotalRaw = getTotalRate(room.dailyRates);
    console.log('Subtotal de la habitación calculado:', roomSubtotalRaw);
      const taxesIncluded = reservation.balanceDetailed.taxesFees;
      const isTaxable = taxesIncluded === 0 ? true : false;

      const IVA_RATE = 0.16; // Tasa de IVA (16%)
      const ISH_RATE = 0.05; // Tasa de ISH (5%)
      
      let ish = 0;
      let iva = 0;
      let roomSubtotal = 0;
      let roomTotal = 0;
      
if (isTaxable) {
            // Los impuestos NO están incluidos
            roomSubtotal = round6(roomSubtotalRaw);
            
            // Calculamos impuestos directos sobre la base
            iva = round6(roomSubtotal * IVA_RATE);
            ish = round6(roomSubtotal * ISH_RATE);
            
            // Total es la suma de todo
            roomTotal = round6(roomSubtotal + iva + ish);
            
        } else {
            // Obtenemos el factor de impuestos basado en la fecha de la reserva
            const taxFactor = 1 + (ishWithIvaPercent[reservation.startDate.slice(0, 4)] || ishWithIvaPercent['default']);
            
            // Obtenemos el Subtotal (Base) dividiendo el total entre el factor
            roomSubtotal = round6(roomSubtotalRaw / taxFactor);
            
            // Calculamos los impuestos a partir del subtotal
            iva = round6(roomSubtotal * IVA_RATE);
            ish = round6(roomSubtotal * ISH_RATE); 
            
            // El total ya está dado por roomSubtotalRaw, pero lo recalculamos para asegurarnos de que todo cuadre
            roomTotal = round6(roomSubtotal + iva + ish);
        }
    
    return {
      ProductCode: "90111500", //Código estándar para servicios de alojamiento
      IdentificationNumber: `${String(index + 1).padStart(3, "0")}-${
        room.roomName
      }`, //Número de identificación único para la habitación
      Description: `SERVICIO DE ALOJAMIENTO - HAB ${room.roomName} - ${room.roomTypeName}`, //Descripción del servicio con detalles de la habitación
      Unit: "NO APLICA", //Unidad de medida, "NO APLICA" para servicios
      UnitCode: "E48", //Código de unidad de medida estándar para servicios
      UnitPrice: roomSubtotal, //Precio unitario del servicio
      Quantity: 1, //Cantidad de noches o servicios, generalmente 1 por Item
      Subtotal: roomSubtotal, //Subtotal antes de impuestos
      Discount: 0.0, //Descuento aplicado, si es que hay alguno
      TaxObject: "02", //Objeto del impuesto, "02" para servicios gravados
      sub_reservation_id: room.subReservationID ?? null, // ID de la sub-reservación para vincular con la factura

      // Impuestos aplicables: solo incluir cuando el Objeto de Impuesto sea "02"
        Taxes: [
        {
          Total: iva, //Total del impuesto
          Name: "IVA", //Nombre del impuesto
          Base: roomSubtotal, //Base sobre la cual se calcula el impuesto
          Rate: IVA_RATE, //Tasa del impuesto (16% en este caso)
          IsRetention: false, //Indica si es una retención o un traslado
        },
        {
          Total: ish, //Total del ISH (0 si no aplica)
          Name: "ISH", //Nombre del impuesto local
          Base: roomSubtotal, //Base sobre la cual se calcula el ISH
          Rate: ISH_RATE, //Tasa del ISH (5%)
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
