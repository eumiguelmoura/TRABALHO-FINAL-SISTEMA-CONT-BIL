<template>
  <q-layout view="lHh Lpr lFf" class="login-bg">
    <q-page-container>
      <q-page class="flex flex-center q-pa-md">
        <q-card class="login-card shadow-15">
          <q-card-section class="text-center q-pt-xl q-pb-lg">
            <div class="logo-mark q-mx-auto"><q-icon name="account_balance" size="38px" /></div>
            <div class="text-h5 text-weight-bold q-mt-md">Sistema Contábil</div>
            <div class="text-grey-7 q-mt-xs">Acesse sua área de lançamentos e impostos</div>
          </q-card-section>
          <q-card-section class="q-px-xl q-pb-xl">
            <q-form @submit.prevent="login" class="q-gutter-md">
              <q-input outlined v-model.trim="username" label="Usuário (e-mail)" type="email" autocomplete="username" :rules="[v => !!v || 'Informe seu usuário']">
                <template #prepend><q-icon name="person_outline" /></template>
              </q-input>
              <q-input outlined v-model="password" label="Senha" :type="showPassword ? 'text' : 'password'" autocomplete="current-password" :rules="[v => !!v || 'Informe a senha']">
                <template #prepend><q-icon name="lock_outline" /></template>
                <template #append><q-icon :name="showPassword ? 'visibility_off' : 'visibility'" class="cursor-pointer" @click="showPassword = !showPassword" /></template>
              </q-input>
              <q-banner v-if="error" class="bg-red-1 text-red-9 rounded-borders">{{ error }}</q-banner>
              <q-btn type="submit" unelevated class="full-width q-py-sm" color="primary" label="Entrar no sistema" :loading="loading" icon-right="login" />
            </q-form>
          </q-card-section>
        </q-card>
      </q-page>
    </q-page-container>
  </q-layout>
</template>
<script setup>
import { ref } from 'vue'
import { LocalStorage } from 'quasar'
import { useRouter } from 'vue-router'
import { api } from 'src/boot/axios'
const router=useRouter()
const username=ref(''), password=ref(''), loading=ref(false), error=ref(''), showPassword=ref(false)
async function login() {
  loading.value=true; error.value=''
  try {
    const { data }=await api.post('/auth/login',{ username:username.value,password:password.value })
    LocalStorage.set('auth_data',data)
    await router.replace('/admin')
  } catch(e) {error.value=e.response?.data?.message || 'Usuário ou senha incorretos.'}
  finally { loading.value=false }
}
</script>
