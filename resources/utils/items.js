import { calculateIsh } from "./helpers.js";
import { additionalItems } from "./additionalItems.js";
import { ishWithIvaPercent } from "./helpers.js";

export const items = (reservation) => {
  const taxesIncluded = reservation.balanceDetailed.taxesFees;
  const isTaxable = taxesIncluded === 0 ? true : false;
  const reservationYear = reservation.startDate.slice(0, 4);

  // Generar un item por cada dailyRate
  const roomItems = reservation.assigned.flatMap((room, roomIndex) => {
    if (!Array.isArray(room.dailyRates) || room.dailyRates.length === 0) {
      return [];
    }

    return room.dailyRates.map((dailyRate, rateIndex) => {
      let roomSubtotal = Number(dailyRate.rate || 0);
      let roomTotal = 0;
      let iva = 0;
      let ish = 0;

      if (!isTaxable) {
        const ishNotTaxable = calculateIsh(roomSubtotal, reservationYear).toFixed(2);
        const ivaNotTaxable = (roomSubtotal * 0.16).toFixed(2);
        const roomTotalNotTaxable =
          roomSubtotal + parseFloat(ivaNotTaxable) + parseFloat(ishNotTaxable);

        ish = parseFloat(ishNotTaxable).toFixed(2);
        iva = parseFloat(ivaNotTaxable).toFixed(2);
        roomTotal = roomTotalNotTaxable.toFixed(2);
      } else {
        // Los impuestos SÍ están incluidos: extraer el subtotal base
        roomTotal = parseFloat(roomSubtotal).toFixed(2); // El total ya incluye los impuestos
        roomSubtotal =
          parseFloat(roomTotal /
          (1 +
            (ishWithIvaPercent[reservationYear] ||
              ishWithIvaPercent["default"]))).toFixed(2); // Calcular el subtotal base
        iva = (roomSubtotal * 0.16).toFixed(2);
        ish = (roomTotal - roomSubtotal - parseFloat(iva)).toFixed(2);
      }

      const rateDate = dailyRate.date ? ` - ${dailyRate.date}` : "";

      return {
      ProductCode: "90111500", //Código estándar para servicios de alojamiento
      IdentificationNumber: `${String(roomIndex + 1).padStart(3, "0")}-${ //Número de identificación único para la habitación
          room.roomName
        }-${String(rateIndex + 1).padStart(3, "0")}`,
      Description: `SERVICIO DE ALOJAMIENTO - HAB ${room.roomName} - ${room.roomTypeName}${rateDate}`, //Descripción del servicio con detalles de la habitación
      Unit: "NO APLICA", //Unidad de medida, "NO APLICA" para servicios
      UnitCode: "E48", //Código de unidad de medida estándar para servicios
      UnitPrice: parseFloat(roomSubtotal).toFixed(2), //Precio unitario del servicio
      Quantity: 1, //Cantidad de noches o servicios, generalmente 1 por Item
      Subtotal: parseFloat(roomSubtotal).toFixed(2), //Subtotal antes de impuestos
      Discount: "0.00", //Descuento aplicado, si es que hay alguno
      TaxObject: "02", //Objeto del impuesto, "02" para servicios gravados
      sub_reservation_id: room.subReservationID ?? null, // ID de la sub-reservación para vincular con la factura

      // Impuestos aplicables: solo incluir cuando el Objeto de Impuesto sea "02"
        Taxes: [
        {
          Total: parseFloat(iva).toFixed(2), //Total del impuesto
          Name: "IVA", //Nombre del impuesto
          Base: parseFloat(roomSubtotal).toFixed(2), //Base sobre la cual se calcula el impuesto
          Rate: "0.16", //Tasa del impuesto (16% en este caso)
          IsRetention: false, //Indica si es una retención o un traslado
        },
        {
          Total: parseFloat(ish).toFixed(2), //Total del ISH (0 si no aplica)
          Name: "ISH", //Nombre del impuesto local
          Base: parseFloat(roomSubtotal).toFixed(2), //Base sobre la cual se calcula el ISH
          Rate: "0.05", //Tasa del ISH (5%)
          IsRetention: false, //Indica si es una retención o un traslado
        }
        ],
      //Total después de impuestos
      Total: parseFloat(roomTotal).toFixed(2),
      };
    });
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