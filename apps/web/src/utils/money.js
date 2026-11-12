// O servidor Laravel sempre recalcula e valida os totais; valores aqui são apenas prévias.
export const cents = value => Math.round(Number(value || 0) * 100)
export const amount = value => (Math.round(value) / 100).toFixed(2)
export const money = value => new Intl.NumberFormat('pt-BR',{style:'currency',currency:'BRL'}).format(Number(value || 0))
export const dateBR = value => value ? String(value).substring(0,10).split('-').reverse().join('/') : ''
export const itemTotals = (row, options = {}) => {
  const gross = cents(Number(row.quantity || 0) * Number(row.unit_price || 0))
  const icms = Math.round(cents(row.icms_base) * Math.round(Number(row.icms_rate || 0) * 10000) / 1000000)
  const ipi = Math.round(gross * Math.round(Number(row.ipi_rate || 0) * 10000) / 1000000)
  const net = gross + (options.add_ipi_to_total !== false ? ipi : 0) - (options.deduct_icms ? icms : 0)
  return { gross_total: amount(gross), icms_amount: amount(icms), ipi_amount: amount(ipi), net_total: amount(net) }
}
