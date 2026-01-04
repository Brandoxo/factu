export const getTotalRate = (dailyRates) => {
  if (!Array.isArray(dailyRates)) return 0;
  return dailyRates.reduce((sum, item) => sum + (item.rate || 0), 0);
};