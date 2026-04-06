const currencySymbols = {
  EUR: '€',
  USD: '$',
  GBP: '£',
  INR: '₹',
  JPY: '¥',
  CAD: 'CA$',
  AUD: 'A$',
  CHF: 'CHF',
}

export function useCurrency() {
  function formatAmount(amount, currency = 'EUR') {
    const num = parseFloat(amount) || 0
    const symbol = currencySymbols[currency] || currency

    return `${symbol}${num.toLocaleString('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    })}`
  }

  function getSymbol(currency = 'EUR') {
    return currencySymbols[currency] || currency
  }

  return { formatAmount, getSymbol, currencySymbols }
}
