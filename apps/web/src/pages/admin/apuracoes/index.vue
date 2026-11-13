<template>
<q-page class="page-wrap q-pa-lg"><div class="row justify-between items-center q-mb-lg"><div><div class="eyebrow">IMPOSTOS</div><h1 class="page-title">Apuração de impostos</h1></div><q-btn outline color="primary" icon="history" label="Impostos apurados" to="/admin/apuracoes/historico" /></div>
<q-banner class="bg-blue-1 text-blue-10 q-mb-md" rounded>Demonstrativo acadêmico dos impostos destacados em <b>notas de entrada</b>. Valores de entrada normalmente representam créditos e não determinam, sozinhos, ICMS ou IPI a pagar no regime fiscal real.</q-banner>
<q-card flat bordered class="panel-card"><q-card-section class="row items-center justify-between"><div><div class="text-h6">Selecione notas não apuradas</div><div class="text-caption text-grey-7">Marque todas ou somente as notas que deseja incluir.</div></div><q-btn flat icon="refresh" color="primary" label="Atualizar" @click="load" /></q-card-section>
<q-separator /><q-card-section><q-checkbox v-model="allSelected" label="Selecionar todas as notas disponíveis" :disable="!notes.length" /><q-separator spaced />
<div v-if="!notes.length" class="text-grey-7 q-pa-md">Nenhuma nota disponível. Cadastre novas notas ou consulte o histórico.</div>
<q-list v-else separator><q-item v-for="note in notes" :key="note.id" tag="label"><q-item-section side><q-checkbox :model-value="selected.includes(note.id)" @update:model-value="v=>toggle(note.id,v)" /></q-item-section><q-item-section><q-item-label class="text-weight-medium">NF {{ note.number }} — {{ note.supplier }}</q-item-label><q-item-label caption>Emissão: {{ dateBR(note.issue_date) }}</q-item-label></q-item-section><q-item-section side class="text-right"><div class="text-weight-bold">{{ money(note.gross_total) }}</div><div class="text-caption">ICMS {{ money(note.icms_total) }} · IPI {{ money(note.ipi_total) }}</div></q-item-section></q-item></q-list>
</q-card-section><q-separator /><q-card-section>
<q-input outlined dense label="Nome da apuração" v-model.trim="name" placeholder="Ex.: Lançamentos novembro/2026" class="q-mb-lg" />
<div class="row q-col-gutter-md q-mb-md"><div class="col-12 col-sm-4"><div class="metric"><div class="text-caption">Notas selecionadas</div><div class="text-h5 text-weight-bold">{{ selected.length }}</div></div></div><div class="col-12 col-sm-4"><div class="metric"><div class="text-caption">ICMS destacado</div><div class="text-h5 text-weight-bold">{{ money(icms) }}</div></div></div><div class="col-12 col-sm-4"><div class="metric"><div class="text-caption">IPI destacado</div><div class="text-h5 text-weight-bold">{{ money(ipi) }}</div></div></div></div>
<q-banner v-if="error" class="bg-red-1 text-red-9 q-mb-md">{{error}}</q-banner>
<div class="row justify-end"><q-btn unelevated color="primary" icon="calculate" label="Apurar impostos" :disable="!selected.length||!name" :loading="saving" @click="assess" /></div>
</q-card-section></q-card>
</q-page>
</template>
<script setup>
import { ref,computed,onMounted } from 'vue'
import { api } from 'src/boot/axios'
import { money,dateBR,cents,amount } from 'src/utils/money'
import { Notify } from 'quasar'
import { useRouter } from 'vue-router'
const router=useRouter(),notes=ref([]),selected=ref([]),loading=ref(false),saving=ref(false),error=ref(''),name=ref(`Lançamentos ${new Date().toLocaleDateString('pt-BR',{month:'long',year:'numeric'})}`)
const allSelected=computed({get:()=>notes.value.length>0&&selected.value.length===notes.value.length,set:v=>{selected.value=v?notes.value.map(n=>n.id):[]}})
const chosen=computed(()=>notes.value.filter(n=>selected.value.includes(n.id)))
const icms=computed(()=>amount(chosen.value.reduce((a,n)=>a+cents(n.icms_total),0)))
const ipi=computed(()=>amount(chosen.value.reduce((a,n)=>a+cents(n.ipi_total),0)))
function toggle(id,check){selected.value=check?[...selected.value,id]:selected.value.filter(i=>i!==id)}
async function load(){loading.value=true;try{notes.value=(await api.get('/invoices?pending_only=1')).data;selected.value=[]}catch{Notify.create({type:'negative',message:'Não foi possível carregar notas'})}finally{loading.value=false}}
async function assess(){if(!selected.value.length || !name.value)return;saving.value=true;error.value='';try{const {data}=await api.post('/tax-assessments',{name:name.value,invoice_ids:selected.value});Notify.create({type:'positive',message:'Apuração registrada'});await router.push(`/admin/apuracoes/${data.id}`)}catch(e){error.value=Object.values(e.response?.data?.errors||{}).flat().join(' | ')||e.response?.data?.message||'Erro na apuração';await load()}finally{saving.value=false}}
onMounted(load)
</script>
