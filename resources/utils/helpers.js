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

export const round6 = (value) => {
  // Redondear a 6 decimales y evitar problemas de precisión con números muy pequeños por ejemplo 95.41157024 = 95.411570
  return Number(value.toString().match(/^-?\d+(?:\.\d{0,6})?/)[0]);
}

const ProductPrecision  = (original1, original2) =>{
  console.log("Original1:", original1);
  console.log("Original2:", original2);
  const scaled1 = original1 * 1e6;
  const scaled2 = original2 * 1e6;
  console.log("Scaled1:", scaled1);
  console.log("Scaled2:", scaled2);
  // Operate on integers
  const resultScaled = scaled1 * scaled2;
  console.log("Scaled to integer:", resultScaled);
  // Convert back to float by dividing by 1 million squared (since we multiplied two numbers)
  const result = resultScaled / 1e12;
  console.log("Result after scaling back:", result);
  console.log("Rounded Result to 6 decimals:", (result)); 
  console.log("================================");
  return (result);
}
const AdditionPrecision = (original1, original2) =>{
  console.log("Original1:", original1);
  console.log("Original2:", original2);
  // convert to integer by multiplying by 1 million
  const scaled1 = original1 * 1e6;
  const scaled2 = original2 * 1e6;
  console.log("Scaled1:", scaled1);
  console.log("Scaled2:", scaled2);
  // Operate on integers
  const resultScaled = scaled1 + scaled2;
  console.log("Scaled to integer:", resultScaled);
  // Convert back to float by dividing by 1 million
  const result = resultScaled / 1e6;
  console.log("Result after scaling back:", result);
  console.log("Rounded Addition Result to 6 decimals:", round6(result)); 
  console.log("================================");
  return (result);
}
const n1 = ProductPrecision(596.322314, 0.16);
const n2 = ProductPrecision(596.322314, 0.05);
const n3 = AdditionPrecision(n1, n2);
const n4 = AdditionPrecision(n3, 596.322314);

console.log("Final Results:");
console.log("Product of 596.322314 and 0.16:", n1);
console.log(round6(n1));
console.log("Round Function");