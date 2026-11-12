<template><q-page class="page-wrap q-pa-lg"><q-btn flat color="primary" icon="arrow_back" label="Voltar" to="/admin/entradas" /><h1 class="page-title q-my-lg">Nota fiscal {{ invoice?.number }}</h1>
<div v-if="invoice && !(invoice.assessments?.length)" class="row justify-end q-gutter-sm q-mb-md"><q-btn outline color="primary" icon="edit" label="Editar nota" :to="`/admin/entradas/editar/${invoice.id}`" /><q-btn color="negative" flat icon="delete" label="Excluir nota" @click="remove" /></div>
<q-inner-loading :showing="loading" /><q-card v-if="invoice" flat bordered class="panel-card q-mb-md"><q-card-section class="row q-col-gutter-md"><div class="col-6 col-sm-3" v-for="i in info" :key="i.label"><div class="text-caption text-grey-7">{{ i.label }}</div><div class="text-weight-bold">{{ i.value }}</div></div></q-card-section></q-card>
<q-card v-if="invoice" flat bordered class="panel-card q-mb-md"><q-card-section class="text-h6">Produtos</q-card-section><q-table flat :rows="invoice.items || []" :columns="columns" row-key="id" :pagination="{ rowsPerPage: 20 }" /></q-card>
<q-card v-if="invoice" flat bordered class="panel-card"><q-card-section class="text-h6">Valores</q-card-section><q-card-section class="row q-col-gutter-md"><div class="col-6 col-md-2" v-for="t in totals" :key="t.label"><div class="text-caption text-grey-7">{{ t.label }}</div><div class="text-weight-bold">{{ money(t.value) }}</div></div></q-card-section><q-card-section class="text-caption text-grey-7">ICMS {{ invoice.deduct_icms ? 'abatido' : 'apenas destacado (sem abatimento)' }} · IPI {{ invoice.add_ipi_to_total ? 'somado ao líquido' : 'sem adição ao líquido' }}.</q-card-section></q-card>
</q-page></template>
<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Notify, Dialog } from 'quasar'
import { api } from 'src/boot/axios'
import { money, dateBR } from 'src/utils/money'
const route=useRoute(),router=useRouter(),invoice=ref(null),loading=ref(false)
const info=computed(()=>invoice.value ? [{label:'Emissão',value:dateBR(invoice.value.issue_date)},{label:'Fornecedor',value:invoice.value.supplier},{label:'Número',value:invoice.value.number},{label:'Cidade / UF',value:`${invoice.value.city}/${invoice.value.state}`}] : [])
const totals=computed(()=>invoice.value ? [{label:'BC ICMS',value:invoice.value.icms_base},{label:'ICMS',value:invoice.value.icms_total},{label:'IPI',value:invoice.value.ipi_total},{label:'Bruto',value:invoice.value.gross_total},{label:'Líquido',value:invoice.value.net_total}] : [])
const columns=[{name:'sku',label:'SKU',field:'sku',align:'left'},{name:'description',label:'Descrição',field:'description',align:'left'},{name:'cfop',label:'CFOP',field:'cfop'},{name:'quantity',label:'Qtd.',field:'quantity'},{name:'gross_total',label:'Bruto',field:r=>money(r.gross_total)},{name:'icms_amount',label:'ICMS',field:r=>money(r.icms_amount)},{name:'ipi_amount',label:'IPI',field:r=>money(r.ipi_amount)}]
function remove(){Dialog.create({title:'Excluir nota?',message:'Esta ação não poderá ser desfeita.',cancel:true,ok:{label:'Excluir',color:'negative'}}).onOk(async()=>{
  try{await api.delete(`/invoices/${route.params.id}`);Notify.create('Nota excluída');router.push('/admin/entradas')}
  catch(e){Notify.create({type:'negative',message:e.response?.data?.message||'Não foi possível excluir'})}
})}
onMounted(async()=>{loading.value=true;try{invoice.value=(await api.get(`/invoices/${route.params.id}`)).data}catch{Notify.create({type:'negative',message:'Erro ao buscar nota'})}finally{loading.value=false}})
</script>
