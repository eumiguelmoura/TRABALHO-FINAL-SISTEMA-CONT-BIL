<template><q-page class="page-wrap q-pa-lg"><q-btn flat icon="arrow_back" color="primary" label="Voltar ao histórico" to="/admin/apuracoes/historico" /><div class="eyebrow q-mt-md">DETALHES DA APURAÇÃO</div><h1 class="page-title q-mb-lg">{{ entry?.name || 'Apuração' }}</h1>
<q-banner class="bg-blue-1 text-blue-10 q-mb-md">Este resultado soma os tributos <b>destacados nas entradas</b>. Para determinar imposto efetivamente a pagar, seria necessária a apuração de débitos/créditos e das demais regras fiscais aplicáveis.</q-banner>
<div class="row q-col-gutter-lg q-mb-lg"><div class="col-12 col-md-4"><q-card flat bordered class="metric"><div class="text-caption">ICMS das notas selecionadas</div><div class="text-h4 text-weight-bold text-primary">{{money(entry?.total_icms)}}</div></q-card></div><div class="col-12 col-md-4"><q-card flat bordered class="metric"><div class="text-caption">IPI das notas selecionadas</div><div class="text-h4 text-weight-bold text-teal-8">{{money(entry?.total_ipi)}}</div></q-card></div><div class="col-12 col-md-4"><q-card flat bordered class="metric"><div class="text-caption">Notas incluídas</div><div class="text-h4 text-weight-bold">{{entry?.invoice_count || 0}}</div></q-card></div></div>
<q-card flat bordered class="panel-card"><q-card-section class="text-h6">Notas integrantes da apuração</q-card-section><q-table flat row-key="id" :rows="entry?.invoices || []" :columns="columns" :pagination="{rowsPerPage:20}" /></q-card>
</q-page></template>
<script setup>
import {ref,onMounted} from 'vue'
import {useRoute} from 'vue-router'
import {Notify} from 'quasar'
import {api} from 'src/boot/axios'
import {money,dateBR} from 'src/utils/money'
const route=useRoute(),entry=ref(null)
const columns=[{name:'issue_date',label:'Emissão',field:r=>dateBR(r.issue_date),align:'left'},{name:'number',label:'Nota',field:'number',align:'left'},{name:'supplier',label:'Cliente',field:'supplier',align:'left'},{name:'gross_total',label:'Valor',field:r=>money(r.gross_total)},{name:'icms_total',label:'ICMS',field:r=>money(r.icms_total)},{name:'ipi_total',label:'IPI',field:r=>money(r.ipi_total)}]
onMounted(async()=>{try{entry.value=(await api.get(`/tax-assessments/${route.params.id}`)).data}catch{Notify.create({type:'negative',message:'Apuração não encontrada'})}})
</script>
