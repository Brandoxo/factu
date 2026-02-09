export const getTotalRate = (dailyRates) => {
  if (!Array.isArray(dailyRates)) return 0;
  const total = dailyRates.reduce((sum, item) => sum + (item.rate || 0), 0);
  return Number((total * 100 / 100).toFixed(6));
};

export const calculateIsh = (subtotal, reservationDate) => {
  let Ish = 0;
  if (reservationDate === '2026') {  
  Ish = 0.05; // Tasa del ISH (5%)
  } else {
  Ish = 0.04; // Tasa del ISH (4%)
  }
  let totalIsh = Number((subtotal * Ish).toFixed(6));
  return totalIsh;
};

export const ishWithIvaPercent = 
{
  '2026': 0.21,
  'default': 0.20
};