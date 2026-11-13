// O servidor Laravel sempre recalcula e valida os totais; valores aqui são apenas prévias.
export const cents = value => Math.round(Number(value || 0) * 100)
export const amount = value => (Math.round(value) / 100).toFixed(2)
export const money = value => new Intl.NumberFormat('pt-BR',{style:'currency',currency:'BRL'}).format(Number(value || 0))
export const dateBR = value => value ? String(value).substring(0,10).split('-').reverse().join('/') : ''
// Cálculo condicionado: BC ICMS = base do IPI + IPI, sem frete/seguro/outras parcelas.
// Nunca apresentar o desconto inferido como um fato quando essa identidade não fecha.
export function discountFromIcmsBase(row) {
  const gross = cents(Number(row.quantity || 0) * Number(row.unit_price || 0))
  const icmsBase = cents(row.icms_base)
  const rate = Math.round(Number(row.ipi_rate || 0) * 10000)
  if (!Number.isSafeInteger(gross) || gross <= 0 || !Number.isSafeInteger(icmsBase) || icmsBase <= 0 || !Number.isSafeInteger(rate) || rate < 0 || rate > 1000000) {
    return { error: 'Informe quantidade, valor unitário, BC ICMS e alíquota IPI válidos para o cálculo automático.' }
  }
  const ipiBase = Math.round(icmsBase * 1000000 / (1000000 + rate))
  const ipi = Math.round(ipiBase * rate / 1000000)
  const discount = gross - ipiBase
  if (ipiBase < 0 || discount < 0 || discount > gross || ipiBase + ipi !== icmsBase) {
    return { error: 'A BC ICMS não corresponde a produto líquido + IPI. Desative o modo automático neste item e use o rateio pelo total da nota ou confira o XML.' }
  }
  return { discount_amount: amount(discount), ipi_base: amount(ipiBase), ipi_amount: amount(ipi), error: null }
}

export const itemTotals = (row, options = {}) => {
  const gross = cents(Number(row.quantity || 0) * Number(row.unit_price || 0))
  const icms = Math.round(cents(row.icms_base) * Math.round(Number(row.icms_rate || 0) * 10000) / 1000000)
  const auto = (row.discount_mode === 'icms_includes_ipi' || row.auto_discount === true) ? discountFromIcmsBase(row) : null
  const discount = auto && !auto.error ? cents(auto.discount_amount) : cents(row.discount_amount)
  const hasOverride = row.ipi_base !== '' && row.ipi_base !== null && row.ipi_base !== undefined
  const ipiBase = auto && !auto.error ? cents(auto.ipi_base) : hasOverride && !auto ? cents(row.ipi_base) : Math.max(0, gross - discount)
  const ipi = Math.round(ipiBase * Math.round(Number(row.ipi_rate || 0) * 10000) / 1000000)
  const net = gross - discount + (options.add_ipi_to_total !== false ? ipi : 0) - (options.deduct_icms ? icms : 0)
  return { gross_total: amount(gross), discount_amount: amount(discount), ipi_base: amount(ipiBase), icms_amount: amount(icms), ipi_amount: amount(ipi), net_total: amount(net), auto_discount_error: auto?.error || null }
}

// Distribui o total proporcionalmente, alocando centavos restantes aos maiores resíduos.
export function proportionalDiscount(grossCents, discountCents) {
  const total = grossCents.reduce((sum, value) => sum + value, 0)
  if (!Number.isSafeInteger(discountCents) || discountCents < 0 || discountCents > total || total <= 0) {
    throw new Error('Desconto total inválido para os valores dos produtos.')
  }
  const result = grossCents.map((gross,index) => ({
    index, value: Math.floor(gross * discountCents / total),
    residue: gross * discountCents % total
  }))
  const remaining = discountCents - result.reduce((sum, value) => sum + value.value, 0)
  const ranked = [...result].sort((a,b) => b.residue - a.residue || a.index-b.index)
  for (let i=0; i<remaining; i++) ranked[i].value++
  return result.map(item => item.value)
}
