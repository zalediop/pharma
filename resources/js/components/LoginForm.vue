<template>
  <div class="min-h-screen flex">
    <!-- Panneau gauche — Branding -->
    <div class="hidden lg:flex lg:w-1/2 flex-col items-center justify-center relative overflow-hidden"
         style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4338ca 70%, #6366f1 100%);">
      <!-- Décorations de fond -->
      <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-24 -right-24 h-96 w-96 rounded-full bg-white/5"></div>
        <div class="absolute -bottom-32 -left-32 h-80 w-80 rounded-full bg-white/5"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 h-[500px] w-[500px] rounded-full bg-indigo-400/10 blur-3xl"></div>
      </div>

      <div class="relative z-10 text-center px-12 animate-fade-in">
        <!-- Icône principale -->
        <div class="mx-auto mb-8 flex h-24 w-24 items-center justify-center rounded-3xl bg-white/15 backdrop-blur-sm shadow-2xl ring-1 ring-white/20">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
          </svg>
        </div>
        <h1 class="text-4xl font-bold text-white mb-4 tracking-tight">PharmaGest</h1>
        <p class="text-indigo-200 text-lg leading-relaxed max-w-sm">
          Gérez votre pharmacie intelligemment — stock, ventes, ordonnances et fournisseurs en un seul endroit.
        </p>

        <!-- Features -->
        <div class="mt-10 space-y-3 text-left max-w-xs mx-auto">
          <div v-for="feature in features" :key="feature" class="flex items-center gap-3 text-indigo-100 text-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
            </svg>
            {{ feature }}
          </div>
        </div>
      </div>
    </div>

    <!-- Panneau droit — Formulaire -->
    <div class="flex flex-1 items-center justify-center bg-slate-50 px-6 py-12">
      <div class="w-full max-w-md animate-slide-up">
        <!-- Logo mobile -->
        <div class="mb-8 flex items-center gap-3 lg:hidden">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
            </svg>
          </div>
          <span class="text-xl font-bold text-slate-900">PharmaGest</span>
        </div>

        <div class="rounded-2xl bg-white p-8 shadow-xl ring-1 ring-slate-100">
          <div class="mb-8">
            <h2 class="text-2xl font-bold text-slate-900">Connexion</h2>
            <p class="mt-1 text-sm text-slate-500">Entrez vos identifiants pour accéder à l'application.</p>
          </div>

          <form @submit.prevent="submit" class="space-y-5">
            <!-- Email -->
            <div>
              <label class="input-label">Email</label>
              <div class="relative mt-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                  </svg>
                </div>
                <input
                  v-model="form.email"
                  type="email"
                  required
                  placeholder="admin@example.com"
                  class="input-field pl-10"
                />
              </div>
            </div>

            <!-- Mot de passe -->
            <div>
              <label class="input-label">Mot de passe</label>
              <div class="relative mt-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                </div>
                <input
                  v-model="form.password"
                  type="password"
                  required
                  placeholder="••••••••"
                  class="input-field pl-10"
                />
              </div>
            </div>

            <!-- Erreur -->
            <div v-if="error" class="flex items-center gap-2 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700 ring-1 ring-red-100">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
              {{ error }}
            </div>

            <!-- Bouton connexion -->
            <button
              type="submit"
              :disabled="loading"
              class="btn-primary w-full justify-center py-3"
            >
              <svg v-if="loading" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
              </svg>
              {{ loading ? 'Connexion...' : 'Se connecter' }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';

const emit = defineEmits(['authenticated']);

const form = reactive({ email: '', password: '' });
const error = ref('');
const loading = ref(false);

const features = [
  'Gestion du stock en temps réel',
  'Suivi des ventes et ordonnances',
  'Alertes d\'expiration automatiques',
  'Gestion des fournisseurs',
];

async function submit() {
  error.value = '';
  loading.value = true;
  try {
    const response = await window.axios.post('/api/auth/login', form);
    emit('authenticated', response.data);
  } catch (exception) {
    error.value = exception.response?.data?.message ?? 'Identifiants incorrects. Veuillez réessayer.';
  } finally {
    loading.value = false;
  }
}
</script>
