<template><q-page class="flex flex-center"><q-spinner size="40px" /></q-page></template>
<script setup>import { onMounted } from 'vue'; import { LocalStorage } from 'quasar'; import { useRouter } from 'vue-router'; const router = useRouter(); onMounted(() => router.replace(LocalStorage.getItem('auth_data')?.token ? '/admin' : '/login'));</script>
