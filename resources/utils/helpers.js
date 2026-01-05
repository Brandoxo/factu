export const getTotalRate = (dailyRates) => {
  if (!Array.isArray(dailyRates)) return 0;
  const total = dailyRates.reduce((sum, item) => sum + (item.rate || 0), 0);
  // Ensure we return a number to avoid string concatenation downstream
  return Number(total.toFixed(2));
};
