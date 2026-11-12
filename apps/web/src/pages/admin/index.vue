<template>
  <q-page class="page-wrap q-pa-lg notranslate" translate="no">
    <div class="eyebrow">PAINEL PRINCIPAL</div>
    <h1 class="page-title">Bem-vindo ao Sistema Contábil</h1>
    <p class="text-grey-7 q-mt-none">Escolha o módulo que deseja acessar.</p>
    <div class="row q-col-gutter-lg q-mt-lg">
      <div class="col-12 col-md-6"><q-card class="module-card cursor-pointer" @click="$router.push('/admin/entradas')">
        <q-card-section><div class="module-icon bg-blue-1 text-primary"><q-icon name="note_add" size="40px" /></div>
          <div class="text-h5 text-weight-bold q-mt-lg notranslate">Entrada de notas</div><p class="text-grey-7">Cadastre notas fiscais e seus produtos com cálculo automático de ICMS, IPI e total líquido.</p>
          <q-btn outline color="primary" icon-right="arrow_forward" label="Acessar entradas" /></q-card-section>
      </q-card></div>
      <div class="col-12 col-md-6"><q-card class="module-card cursor-pointer" @click="$router.push('/admin/apuracoes')">
        <q-card-section><div class="module-icon bg-teal-1 text-teal-8"><q-icon name="calculate" size="40px" /></div>
          <div class="text-h5 text-weight-bold q-mt-lg notranslate">Apuração de impostos</div><p class="text-grey-7">Selecione notas, consolide seus valores e consulte o histórico de apurações realizadas.</p>
          <q-btn outline color="teal-8" icon-right="arrow_forward" label="Acessar apurações" /></q-card-section>
      </q-card></div>
    </div>
  </q-page>
</template>
