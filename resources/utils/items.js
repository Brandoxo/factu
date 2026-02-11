import { getTotalRate } from "./helpers";
import { calculateIsh } from "./helpers.js";
import { additionalItems } from "./additionalItems.js";
import { ishWithIvaPercent } from "./helpers.js";
import { formatDecimal6 } from "./helpers";

export const items = (reservation) => {
  // Generar items de habitaciones
  const roomItems = reservation.assigned.map((room, index) => {
    let roomSubtotal = getTotalRate(room.dailyRates);
    console.log('Subtotal de la habitación calculado:', roomSubtotal);
      const taxesIncluded = reservation.balanceDetailed.taxesFees;
      const isTaxable = taxesIncluded === 0 ? true : false;

      let roomTotal = 0;
      let iva = 0;
      let ish = 0;
      
      let ishNotTaxable = calculateIsh(roomSubtotal, reservation.startDate.slice(0,4)).toFixed(6);
      let ivaNotTaxable = (roomSubtotal * 0.16).toFixed(6);
      let roomTotalNotTaxable = (parseFloat(roomSubtotal) + parseFloat(ivaNotTaxable) + parseFloat(ishNotTaxable)).toFixed(6);

      let ivaTaxable = (roomSubtotal * 0.16).toFixed(6);
      let ishTaxable = (roomTotal - roomSubtotal - iva).toFixed(6);
      let roomSubTotalTaxable = (roomTotal / (1 + ishWithIvaPercent[reservation.startDate.slice(0,4)] || ishWithIvaPercent['default'])).toFixed(6);
      
      if (!isTaxable) {
        // Los impuestos NO están incluidos: calcular sobre el subtotal
        ish = ishNotTaxable;
        iva = ivaNotTaxable;
        roomTotal = roomTotalNotTaxable;
        console.log('Impuestos NO incluidos - Subtotal:', roomSubtotal, 'IVA:', iva, 'ISH:', ish, 'Total:', roomTotal);
      } else {
        // Los impuestos SÍ están incluidos: extraer el subtotal base
        roomTotal = roomSubtotal; // El total ya incluye los impuestos
        roomSubtotal = roomSubTotalTaxable; // Dividir entre 1.21 (1 + 0.16 + 0.04)
        console.log('Subtotal base calculado de total con impuestos incluidos:', roomSubtotal);
        iva = ivaTaxable;
        ish = ishTaxable; // Ajustar ISH para que cuadre exacto
        console.log('Impuestos incluidos - Total:', roomTotal, 'Subtotal base:', roomSubtotal, 'IVA:', iva, 'ISH:', ish);
      }
    
    return {
      ProductCode: "90111500", //Código estándar para servicios de alojamiento
      IdentificationNumber: `${String(index + 1).padStart(3, "0")}-${
        room.roomName
      }`, //Número de identificación único para la habitación
      Description: `SERVICIO DE ALOJAMIENTO - HAB ${room.roomName} - ${room.roomTypeName}`, //Descripción del servicio con detalles de la habitación
      Unit: "NO APLICA", //Unidad de medida, "NO APLICA" para servicios
      UnitCode: "E48", //Código de unidad de medida estándar para servicios
      UnitPrice: parseFloat(roomSubtotal).toFixed(6), //Precio unitario del servicio
      Quantity: 1, //Cantidad de noches o servicios, generalmente 1 por Item
      Subtotal: parseFloat(roomSubtotal).toFixed(6), //Subtotal antes de impuestos
      Discount: "0.000000", //Descuento aplicado, si es que hay alguno
      TaxObject: "02", //Objeto del impuesto, "02" para servicios gravados
      sub_reservation_id: room.subReservationID ?? null, // ID de la sub-reservación para vincular con la factura

      // Impuestos aplicables: solo incluir cuando el Objeto de Impuesto sea "02"
        Taxes: [
        {
          Total: parseFloat(iva).toFixed(6), //Total del impuesto
          Name: "IVA", //Nombre del impuesto
          Base: parseFloat(roomSubtotal).toFixed(6), //Base sobre la cual se calcula el impuesto
          Rate: "0.160000", //Tasa del impuesto (16% en este caso)
          IsRetention: false, //Indica si es una retención o un traslado
        },
        {
          Total: parseFloat(ish).toFixed(6), //Total del ISH (0 si no aplica)
          Name: "ISH", //Nombre del impuesto local
          Base: parseFloat(roomSubtotal).toFixed(6), //Base sobre la cual se calcula el ISH
          Rate: "0.050000", //Tasa del ISH (5%)
          IsRetention: false, //Indica si es una retención o un traslado
        }
        ],
      //Total después de impuestos
      Total: parseFloat(roomTotal).toFixed(6),
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