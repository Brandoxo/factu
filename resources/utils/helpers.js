export const getTotalRate = (dailyRates) => {
  if (!Array.isArray(dailyRates)) return 0;
  const total = dailyRates.reduce((sum, item) => sum + (item.rate || 0), 0);
  return total;
};

export const calculateIsh = (subtotal, reservationDate) => {
  let Ish = 0;
  if (reservationDate === '2026') {  
  Ish = 0.05; // Tasa del ISH (5%)
  } else {
  Ish = 0.04; // Tasa del ISH (4%)
  }
  return subtotal * Ish;
};

export const ishWithIvaPercent = 
{
  '2026': 0.21,
  'default': 0.20
};


export const formatDecimal6 = (value) => {
  return Math.round(value * 1e6);
}

const ProductPrecision  = (original1, original2) =>{
  const scaled1 = original1 * 1e6;
  const scaled2 = original2 * 1e6;
  // Operate on integers
  const resultScaled = scaled1 * scaled2;
  // Convert back to float by dividing by 1 million squared (since we multiplied two numbers)
  const result = resultScaled / 1e12;
  return (result);
}
const AdditionPrecision = (original1, original2) =>{
  // convert to integer by multiplying by 1 million
  const scaled1 = formatDecimal6(original1) * 1e6;
  const scaled2 = formatDecimal6(original2) * 1e6;
  // Operate on integers
  const resultScaled = scaled1 + scaled2;
  // Convert back to float by dividing by 1 million
  const result = resultScaled / 1e6;
  return (result);
}