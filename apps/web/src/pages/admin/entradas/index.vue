<template>
  <q-page class="page-wrap q-pa-lg">
    <div class="row items-center justify-between q-gutter-sm q-mb-md">
      <div><div class="eyebrow">NOTAS FISCAIS</div><h1 class="page-title">LANÇAMENTO DE NOTAS</h1></div>
      <q-btn color="primary" icon="add" label="Novo lançamento" to="/admin/entradas/nova" unelevated />
    </div>
    <q-card flat bordered class="panel-card">
      <q-card-section class="row items-center justify-between">
        <span class="text-subtitle1 text-weight-bold">Notas cadastradas</span>
        <q-btn flat dense color="primary" icon="refresh" label="Atualizar" @click="load" />
      </q-card-section>
      <q-separator />
      <q-table :rows="rows" :columns="columns" row-key="id" :loading="loading" :pagination="{rowsPerPage:10}" flat no-data-label="Nenhuma nota cadastrada">
        <template #body-cell-issue_date="props"><q-td :props="props">{{ dateBR(props.row.issue_date) }}</q-td></template>
        <template #body-cell-gross_total="props"><q-td :props="props">{{ money(props.row.gross_total) }}</q-td></template>
        <template #body-cell-status="props"><q-td :props="props"><q-chip dense :color="props.row.assessed ? 'blue-1' : 'orange-1'" :text-color="props.row.assessed ? 'blue-9':'orange-9'">{{ props.row.assessed ? 'Apurada':'Pendente' }}</q-chip></q-td></template>
        <template #body-cell-actions="props"><q-td :props="props"><q-btn flat dense color="primary" icon="visibility" :to="`/admin/entradas/${props.row.id}`"><q-tooltip>Visualizar nota</q-tooltip></q-btn>
          <q-btn v-if="!props.row.assessed" flat dense color="secondary" icon="edit" :to="`/admin/entradas/editar/${props.row.id}`"><q-tooltip>Editar nota</q-tooltip></q-btn>
          <q-btn v-if="!props.row.assessed" flat dense color="negative" icon="delete_outline" @click="remove(props.row)"><q-tooltip>Excluir nota</q-tooltip></q-btn></q-td></template>
      </q-table>
    </q-card>
  </q-page>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import { api } from 'src/boot/axios'
import { money, dateBR } from 'src/utils/money'
import { Notify, Dialog } from 'quasar'
const rows=ref([]),loading=ref(false)
const columns=[
  {name:'issue_date',label:'Emissão',field:'issue_date',align:'left',sortable:true},
  {name:'number',label:'Número',field:'number',align:'left',sortable:true},
  {name:'supplier',label:'Cliente',field:'supplier',align:'left',sortable:true},
  {name:'gross_total',label:'Valor bruto',field:'gross_total',align:'right'},
  {name:'status',label:'Apuração',field:'status',align:'left'},
  {name:'actions',label:'',field:'id',align:'right'}]
async function load(){loading.value=true;try{const {data}=await api.get('/invoices');rows.value=data.map(i=>({...i,assessed:(i.assessments_count??0)>0}))}catch(e){Notify.create({type:'negative',message:e.response?.data?.message||'Erro ao buscar notas'})}finally{loading.value=false}}
function remove(note){
  Dialog.create({title:'Excluir nota?',message:`Excluir definitivamente a NF ${note.number}?`,cancel:true,ok:{label:'Excluir',color:'negative'}})
    .onOk(async()=>{try{await api.delete(`/invoices/${note.id}`);Notify.create('Nota excluída');await load()}catch(e){Notify.create({type:'negative',message:e.response?.data?.message||'Exclusão bloqueada'})}})
}
onMounted(load)
</script>
