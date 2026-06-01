<template>
  <div class="rounded-xl bg-white p-8 shadow-sm">
    <h2 class="mb-4 text-2xl font-semibold">Connexion</h2>
    <form @submit.prevent="submit" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-slate-700">Email</label>
        <input v-model="form.email" type="email" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2 focus:border-sky-500 focus:outline-none" />
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700">Mot de passe</label>
        <input v-model="form.password" type="password" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2 focus:border-sky-500 focus:outline-none" />
      </div>

      <button type="submit" class="rounded bg-sky-600 px-4 py-2 text-white hover:bg-sky-700">Se connecter</button>
      <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
    </form>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';

const emit = defineEmits(['authenticated']);

const form = reactive({ email: '', password: '' });
const error = ref('');

async function submit() {
  error.value = '';

  try {
    const response = await window.axios.post('/api/auth/login', form);
    const payload = response.data;
    emit('authenticated', payload);
  } catch (exception) {
    error.value = exception.response?.data?.message ?? 'Impossible de se connecter';
  }
}
</script>
