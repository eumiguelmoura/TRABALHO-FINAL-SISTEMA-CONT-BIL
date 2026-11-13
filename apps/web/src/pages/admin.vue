<template>
  <q-layout view="lHh Lpr lFf" class="notranslate" translate="no">
    <q-header class="app-header" elevated><q-toolbar class="q-px-md">
      <q-btn flat dense round icon="menu" @click="drawer = !drawer" aria-label="Abrir menu" />
      <q-toolbar-title class="text-weight-bold">Sistema Contábil</q-toolbar-title>
      <div class="gt-xs text-right q-mr-md"><div class="text-weight-medium">{{ user?.name }}</div><div class="text-caption opacity-70">{{ user?.email }}</div></div>
      <q-btn flat round icon="logout" @click="signOut"><q-tooltip>Sair do sistema</q-tooltip></q-btn>
    </q-toolbar></q-header>
    <q-drawer v-model="drawer" show-if-above bordered :width="270" class="bg-white">
      <q-list padding>
        <q-item-label header class="text-overline text-grey-6">NAVEGAÇÃO</q-item-label>
        <q-item clickable v-ripple to="/admin" exact active-class="menu-active"><q-item-section avatar><q-icon name="dashboard" /></q-item-section><q-item-section><span class="notranslate">Início</span></q-item-section></q-item>
        <q-item clickable v-ripple to="/admin/entradas" active-class="menu-active"><q-item-section avatar><q-icon name="receipt_long" /></q-item-section><q-item-section><span class="notranslate">LANÇAMENTO DE NOTAS</span></q-item-section></q-item>
        <q-item clickable v-ripple to="/admin/apuracoes" active-class="menu-active"><q-item-section avatar><q-icon name="calculate" /></q-item-section><q-item-section><span class="notranslate">Apuração de impostos</span></q-item-section></q-item>
        <q-item clickable v-ripple to="/admin/apuracoes/historico" active-class="menu-active"><q-item-section avatar><q-icon name="history" /></q-item-section><q-item-section><span class="notranslate">Impostos apurados</span></q-item-section></q-item>
      </q-list>
    </q-drawer>
    <q-page-container><router-view /></q-page-container>
  </q-layout>
</template>
<script setup>
import { ref, computed } from 'vue'
import { LocalStorage, Notify } from 'quasar'
import { useRouter } from 'vue-router'
import { api } from 'src/boot/axios'
const router=useRouter(),drawer=ref(false)
const user=computed(()=>LocalStorage.getItem('auth_data')?.user)
async function signOut() {
  try { await api.post('/auth/logout') } catch { /* limpar sessão local mesmo offline */ }
  LocalStorage.remove('auth_data'); Notify.create('Sessão encerrada');router.replace('/login')
}
</script>
