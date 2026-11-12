<template><q-page class="page-wrap q-pa-lg"><div class="row items-center justify-between q-mb-lg"><div><div class="eyebrow">CONSULTA</div><h1 class="page-title">Impostos apurados</h1></div><q-btn outline color="primary" label="Nova apuração" icon="add" to="/admin/apuracoes" /></div>
<q-card flat bordered class="panel-card"><q-list separator><q-item v-for="a in rows" :key="a.id" clickable :to="`/admin/apuracoes/${a.id}`"><q-item-section avatar><q-avatar color="blue-1" text-color="primary" icon="fact_check" /></q-item-section><q-item-section><q-item-label class="text-weight-bold">{{ a.name }}</q-item-label><q-item-label caption>{{ dateBR(a.created_at) }} · {{ a.invoice_count }} nota(s)</q-item-label></q-item-section><q-item-section side class="text-right"><span class="text-weight-medium">ICMS {{ money(a.total_icms) }}</span><span class="text-caption">IPI {{ money(a.total_ipi) }}</span></q-item-section><q-item-section side><q-icon name="chevron_right" /></q-item-section></q-item><q-item v-if="!rows.length"><q-item-section class="text-grey-7">Nenhuma apuração registrada.</q-item-section></q-item></q-list><q-inner-loading :showing="loading" /></q-card></q-page></template>
<script setup>
import { ref,onMounted } from 'vue'
import { api } from 'src/boot/axios'
import { Notify } from 'quasar'
import { money,dateBR } from 'src/utils/money'
const rows=ref([]),loading=ref(false)
onMounted(async()=>{loading.value=true;try{rows.value=(await api.get('/tax-assessments')).data}catch{Notify.create({type:'negative',message:'Erro ao carregar apurações'})}finally{loading.value=false}})
</script>
