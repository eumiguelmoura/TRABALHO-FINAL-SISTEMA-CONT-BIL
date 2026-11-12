<template>
  <q-page class="page-wrap q-pa-lg notranslate" translate="no">
    <div class="row items-center q-mb-lg q-gutter-sm">
      <q-btn flat icon="arrow_back" label="Voltar" to="/admin/entradas" />
      <div>
        <div class="eyebrow">CADASTRO</div>
        <h1 class="page-title">{{ editingId ? 'Editar nota fiscal' : 'Nova entrada de nota fiscal' }}</h1>
      </div>
    </div>

    <form novalidate @submit.prevent="save">
      <q-card flat bordered class="panel-card q-mb-lg">
        <q-card-section>
          <div class="text-h6">Dados da nota</div>
          <div class="text-caption text-grey-7">Preencha o cabeçalho conforme a nota recebida.</div>
        </q-card-section>
        <q-card-section class="row q-col-gutter-md">
          <div class="col-12 col-sm-3">
            <label class="field-label notranslate" for="issue_date">Data de emissão *</label>
            <q-input id="issue_date" v-model="form.issue_date" outlined dense type="date" />
          </div>
          <div class="col-12 col-sm-5">
            <label class="field-label notranslate" for="supplier">Fornecedor *</label>
            <q-input id="supplier" v-model.trim="form.supplier" outlined dense maxlength="180" />
          </div>
          <div class="col-12 col-sm-4">
            <label class="field-label notranslate" for="number">Número da nota *</label>
            <q-input id="number" v-model.trim="form.number" outlined dense maxlength="30" />
          </div>
          <div class="col-12 col-sm-8">
            <label class="field-label notranslate" for="city">Cidade *</label>
            <q-input id="city" v-model.trim="form.city" outlined dense maxlength="100" />
          </div>
          <div class="col-12 col-sm-4">
            <label class="field-label notranslate" for="state">Estado (UF) *</label>
            <q-select
              id="state" v-model="form.state" outlined dense
              :options="ufOptions" option-label="label" option-value="value"
              emit-value map-options options-dense
            />
          </div>
        </q-card-section>
      </q-card>

      <q-card flat bordered class="panel-card q-mb-lg">
        <q-card-section class="row items-center justify-between q-gutter-sm">
          <div>
            <div class="text-h6">Produtos da nota</div>
            <div class="text-caption text-grey-7">O ICMS é calculado por item, mas não reduz o total líquido automaticamente.</div>
          </div>
          <q-btn outline color="primary" icon="add" label="Adicionar produto" @click="addItem" />
        </q-card-section>
        <q-separator />
        <q-card-section v-for="(item, index) in form.items" :key="item._id" class="q-pb-lg">
          <div class="row items-center justify-between q-mb-sm">
            <div class="text-subtitle1 text-weight-bold">Produto {{ index + 1 }}</div>
            <q-btn flat dense icon="delete_outline" color="negative" :disable="form.items.length === 1" @click="removeItem(index)" />
          </div>
          <div class="row q-col-gutter-md">
            <div class="col-12 col-md-3">
              <label class="field-label notranslate">Código / SKU *</label>
              <q-input v-model.trim="item.sku" outlined dense maxlength="70" />
            </div>
            <div class="col-12 col-md-6">
              <label class="field-label notranslate">Descrição *</label>
              <q-input v-model.trim="item.description" outlined dense maxlength="250" />
            </div>
            <div class="col-12 col-md-3">
              <label class="field-label notranslate">CFOP *</label>
              <q-input v-model.trim="item.cfop" outlined dense mask="####" />
            </div>
            <div class="col-12 col-sm-4 col-md-2">
              <label class="field-label notranslate">Quantidade *</label>
              <q-input v-model.number="item.quantity" outlined dense type="number" min="0.001" max="999999" step="0.001" />
            </div>
            <div class="col-12 col-sm-4 col-md-2">
              <label class="field-label notranslate">Valor unitário *</label>
              <q-input v-model.number="item.unit_price" outlined dense type="number" min="0.01" max="99999999" step="0.01" />
            </div>
            <div class="col-12 col-sm-4 col-md-2">
              <label class="field-label notranslate">Total bruto do item</label>
              <q-input outlined dense :model-value="money(calc(item).gross_total)" readonly bg-color="grey-2" />
            </div>
            <div class="col-12 col-sm-4 col-md-2">
              <label class="field-label notranslate">Base de cálculo do ICMS *</label>
              <q-input v-model.number="item.icms_base" outlined dense type="number" min="0" step="0.01" />
            </div>
            <div class="col-12 col-sm-4 col-md-2">
              <label class="field-label notranslate">Alíquota ICMS (%) *</label>
              <q-input v-model.number="item.icms_rate" outlined dense type="number" min="0" max="100" step="0.0001" />
            </div>
            <div class="col-12 col-sm-4 col-md-2">
              <label class="field-label notranslate">ICMS calculado</label>
              <q-input outlined dense :model-value="money(calc(item).icms_amount)" readonly bg-color="grey-2" />
            </div>
            <div class="col-12 col-sm-6 col-md-3">
              <label class="field-label notranslate">Alíquota IPI (%) *</label>
              <q-input v-model.number="item.ipi_rate" outlined dense type="number" min="0" max="100" step="0.0001" />
            </div>
            <div class="col-12 col-sm-6 col-md-3">
              <label class="field-label notranslate">IPI calculado</label>
              <q-input outlined dense :model-value="money(calc(item).ipi_amount)" readonly bg-color="grey-2" />
            </div>
            <div class="col-12 col-md-6">
              <label class="field-label notranslate">Total líquido calculado do item</label>
              <q-input outlined dense :model-value="money(calc(item).net_total)" readonly bg-color="green-1" />
            </div>
          </div>
          <q-separator v-if="index !== form.items.length - 1" class="q-mt-lg" />
        </q-card-section>
      </q-card>

      <q-card flat bordered class="panel-card q-mb-lg">
        <q-card-section>
          <div class="text-h6">Composição do total líquido</div>
          <div class="text-caption text-grey-7">Configure como os impostos destacados aparecem no total da nota. ICMS próprio não é abatido por padrão.</div>
          <q-toggle v-model="form.deduct_icms" label="Deduzir o ICMS do total líquido (somente se aplicável à sua operação)" color="primary" />
          <div><q-toggle v-model="form.add_ipi_to_total" label="Somar o IPI ao total líquido (IPI por fora)" color="primary" /></div>
          <div class="text-caption text-grey-7 q-mt-xs">
            Valor líquido calculado = bruto {{ form.add_ipi_to_total ? '+ IPI' : '(IPI já incluído / sem adição)' }}{{ form.deduct_icms ? ' − ICMS' : '' }}.
            Outros valores como frete e descontos ainda não são calculados neste módulo.
          </div>
        </q-card-section>
        <q-separator />
        <q-card-section class="row items-center justify-between q-gutter-sm">
          <div>
            <div class="text-h6">Totais da nota e conferência</div>
            <div class="text-caption text-grey-7">Os valores destacados nos itens devem bater com os totais do cabeçalho.</div>
          </div>
          <q-btn color="secondary" outline icon="calculate" label="Copiar totais calculados" @click="fillTotals" />
        </q-card-section>
        <q-card-section class="row q-col-gutter-md">
          <div v-for="field in totalFields" :key="field.key" class="col-12 col-sm-6 col-md-4">
            <label class="field-label notranslate">{{ field.label }} *</label>
            <q-input v-model.number="form[field.key]" outlined dense type="number" step="0.01" min="0" />
            <div class="text-caption q-mt-xs" :class="match(field.key) ? 'text-positive' : 'text-negative'">
              {{ match(field.key) ? 'Confere' : 'Divergência' }} — Itens: {{ money(totals[field.key]) }}
            </div>
          </div>
        </q-card-section>
        <q-separator />
        <q-card-section>
          <q-banner rounded :class="headerMatches ? 'bg-green-1 text-green-9' : 'bg-orange-1 text-orange-10'" class="q-mb-md">
            <q-icon :name="headerMatches ? 'verified' : 'warning_amber'" class="q-mr-sm" />
            {{ headerMatches ? 'Totais do cabeçalho conferem com os itens.' : 'Há divergência entre os totais do cabeçalho e a soma dos itens.' }}
          </q-banner>
          <q-banner v-if="formErrors.length" rounded class="bg-red-1 text-negative q-mb-md">
            <div class="text-weight-bold q-mb-xs">Corrija os campos abaixo para gravar:</div>
            <div v-for="(message,index) in formErrors" :key="index">• {{ message }}</div>
          </q-banner>
          <q-banner v-if="serverError" rounded class="bg-red-1 text-negative q-mb-md">{{ serverError }}</q-banner>
          <div class="row justify-end q-gutter-sm">
            <q-btn flat label="Cancelar" to="/admin/entradas" />
            <q-btn color="primary" unelevated icon="save" label="Gravar nota" type="submit" :loading="saving" />
          </div>
        </q-card-section>
      </q-card>
    </form>
  </q-page>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { Notify } from 'quasar'
import { useRoute, useRouter } from 'vue-router'
import { api } from 'src/boot/axios'
import { amount, cents, itemTotals, money } from 'src/utils/money'

const router = useRouter()
const route = useRoute()
const editingId = route.path.includes('/editar/') ? route.params.id : null
const ufNames = {
  AC: 'Acre', AL: 'Alagoas', AP: 'Amapá', AM: 'Amazonas', BA: 'Bahia', CE: 'Ceará',
  DF: 'Distrito Federal', ES: 'Espírito Santo', GO: 'Goiás', MA: 'Maranhão', MT: 'Mato Grosso',
  MS: 'Mato Grosso do Sul', MG: 'Minas Gerais', PA: 'Pará', PB: 'Paraíba', PR: 'Paraná',
  PE: 'Pernambuco', PI: 'Piauí', RJ: 'Rio de Janeiro', RN: 'Rio Grande do Norte',
  RS: 'Rio Grande do Sul', RO: 'Rondônia', RR: 'Roraima', SC: 'Santa Catarina',
  SP: 'São Paulo', SE: 'Sergipe', TO: 'Tocantins'
}
const ufOptions = Object.entries(ufNames).map(([value, name]) => ({ value, label: `${name} (${value})` }))
let nextId = 0
const newItem = () => ({
  _id: ++nextId, sku: '', description: '', cfop: '1102', quantity: 1,
  unit_price: 0, icms_base: 0, icms_rate: 0, ipi_rate: 0
})
const currentLocalDate = () => {
  const now = new Date()
  now.setMinutes(now.getMinutes() - now.getTimezoneOffset())
  return now.toISOString().slice(0, 10)
}
const form = reactive({
  issue_date: currentLocalDate(), supplier: '', number: '', city: '', state: 'PR',
  deduct_icms: false, add_ipi_to_total: true,
  icms_base: 0, icms_total: 0, ipi_total: 0, gross_total: 0, net_total: 0,
  items: [newItem()]
})
const totalFields = [
  { key: 'icms_base', label: 'Valor BC ICMS' }, { key: 'icms_total', label: 'Valor ICMS' },
  { key: 'ipi_total', label: 'Valor IPI' }, { key: 'gross_total', label: 'Total bruto' },
  { key: 'net_total', label: 'Total líquido' }
]
const calc = item => itemTotals(item, form)
const totals = computed(() => form.items.reduce((acc, item) => {
  const values = calc(item)
  acc.icms_base = amount(cents(acc.icms_base) + cents(item.icms_base))
  for (const key of ['gross_total', 'net_total']) acc[key] = amount(cents(acc[key]) + cents(values[key]))
  acc.icms_total = amount(cents(acc.icms_total) + cents(values.icms_amount))
  acc.ipi_total = amount(cents(acc.ipi_total) + cents(values.ipi_amount))
  return acc
}, { icms_base: '0.00', icms_total: '0.00', ipi_total: '0.00', gross_total: '0.00', net_total: '0.00' }))
const match = key => cents(form[key]) === cents(totals.value[key])
const headerMatches = computed(() => totalFields.every(field => match(field.key)))
const saving = ref(false)
const serverError = ref('')
const formErrors = ref([])

function addItem() { form.items.push(newItem()) }
function removeItem(index) { if (form.items.length > 1) form.items.splice(index, 1) }
function fillTotals() {
  totalFields.forEach(field => { form[field.key] = Number(totals.value[field.key]) })
}
function inRange(value, min, max, digits) {
  const n = Number(value)
  if (value === '' || value === null || value === undefined || !Number.isFinite(n) || n < min || n > max) return false
  return /^\d+(?:\.\d+)?$/.test(String(value)) && String(value).split('.')[1]?.length <= digits || /^\d+$/.test(String(value)) && n >= min && n <= max
}
function validate() {
  const issues = []
  if (!/^\d{4}-\d{2}-\d{2}$/.test(form.issue_date) || Number.isNaN(Date.parse(form.issue_date))) issues.push('Informe uma data de emissão válida.')
  if (!String(form.supplier).trim()) issues.push('Informe o fornecedor.')
  if (!String(form.number).trim()) issues.push('Informe o número da nota.')
  if (!String(form.city).trim()) issues.push('Informe a cidade.')
  if (!Object.hasOwn(ufNames, form.state)) issues.push('Selecione uma UF válida.')
  if (form.items.length === 0) issues.push('Adicione pelo menos um produto.')
  for (const [index, item] of form.items.entries()) {
    const errors = []
    if (!String(item.sku).trim()) errors.push('Código/SKU')
    if (!String(item.description).trim()) errors.push('descrição')
    if (!/^\d{4}$/.test(String(item.cfop))) errors.push('CFOP com 4 dígitos')
    if (!inRange(item.quantity, 0.001, 999999, 3)) errors.push('quantidade válida')
    if (!inRange(item.unit_price, 0.01, 99999999, 2)) errors.push('valor unitário válido')
    if (!inRange(item.icms_base, 0, 999999999999, 2)) errors.push('base ICMS válida')
    if (!inRange(item.icms_rate, 0, 100, 4)) errors.push('alíquota ICMS entre 0 e 100')
    if (!inRange(item.ipi_rate, 0, 100, 4)) errors.push('alíquota IPI entre 0 e 100')
    if (cents(calc(item).net_total) < 0) errors.push('total líquido não pode ficar negativo')
    if (errors.length) issues.push(`Produto ${index + 1}: confira ${errors.join(', ')}.`)
  }
  if (!headerMatches.value) {
    const divergent = totalFields.filter(field => !match(field.key)).map(field => field.label)
    issues.push(`Os totais não conferem: ${divergent.join(', ')}. Confira os itens ou use “Copiar totais calculados”.`)
  }
  return issues
}
async function save() {
  formErrors.value = validate()
  serverError.value = ''
  if (formErrors.value.length) return
  saving.value = true
  try {
    const payload = {
      ...form,
      items: form.items.map(item => ({
        sku: item.sku, description: item.description, cfop: item.cfop,
        quantity: item.quantity, unit_price: item.unit_price,
        icms_base: item.icms_base, icms_rate: item.icms_rate, ipi_rate: item.ipi_rate
      }))
    }
    const { data } = editingId
      ? await api.put(`/invoices/${editingId}`, payload)
      : await api.post('/invoices', payload)
    Notify.create({ type: 'positive', message: `Nota ${data.number} gravada com sucesso!` })
    await router.push('/admin/entradas')
  } catch (exception) {
    const errors = exception.response?.data?.errors
    serverError.value = errors
      ? Object.entries(errors).map(([field, messages]) => `${field}: ${messages.join(', ')}`).join(' | ')
      : (exception.response?.data?.message || exception.message || 'Não foi possível gravar a nota.')
  } finally {
    saving.value = false
  }
}
onMounted(async () => {
  if (!editingId) return
  try {
    const { data } = await api.get(`/invoices/${editingId}`)
    for (const key of Object.keys(form)) {
      if (key === 'items') continue
      if (key === 'issue_date') form[key] = String(data[key]).slice(0, 10)
      else if (key === 'deduct_icms') form[key] = Boolean(data[key] ?? false)
      else if (key === 'add_ipi_to_total') form[key] = Boolean(data[key] ?? true)
      else if (totalFields.some(field => field.key === key)) form[key] = Number(data[key])
      else form[key] = data[key]
    }
    form.items = (data.items || []).map(item => ({
      _id: ++nextId, sku: item.sku, description: item.description, cfop: item.cfop,
      quantity: Number(item.quantity), unit_price: Number(item.unit_price),
      icms_base: Number(item.icms_base), icms_rate: Number(item.icms_rate), ipi_rate: Number(item.ipi_rate)
    }))
    if (form.items.length === 0) form.items.push(newItem())
  } catch (exception) {
    serverError.value = exception.response?.data?.message || 'Não foi possível carregar a nota.'
  }
})
</script>

<style scoped>
.field-label {
  display: block;
  margin-bottom: 6px;
  color: #283b55;
  font-size: 13px;
  font-weight: 600;
  line-height: 1.45;
  min-height: 19px;
  white-space: normal;
  overflow-wrap: break-word;
  overflow: visible;
}
</style>
