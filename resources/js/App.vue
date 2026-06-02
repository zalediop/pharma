<template>
  <div class="min-h-screen bg-slate-100 text-slate-900">
    <div class="max-w-6xl mx-auto p-6">
      <header class="mb-8">
        <h1 class="text-3xl font-semibold">Pharmacie Intelligente</h1>
        <p class="text-slate-600 mt-2">Gestion de stock, ventes, ordonnances et fournisseurs.</p>
      </header>

      <LoginForm v-if="!user" @authenticated="handleAuthenticated" />

      <div v-else>
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between mb-6">
          <div>
            <p class="text-slate-700">Connecté en tant que <strong>{{ user.name }}</strong> (<span class="capitalize">{{ user.role }}</span>)</p>
          </div>
          <div class="flex flex-wrap items-center gap-3">
            <nav class="flex flex-wrap gap-2">
              <button v-for="tab in tabs" :key="tab.key" @click="selectedTab = tab.key" :class="['rounded-full px-4 py-2 text-sm font-medium', selectedTab === tab.key ? 'bg-sky-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300']">
                {{ tab.label }}
              </button>
            </nav>
            <button @click="logout" class="rounded bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">Se déconnecter</button>
          </div>
        </div>

        <component :is="activeComponent" :user="user" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue';
import LoginForm from './components/LoginForm.vue';
import Dashboard from './components/Dashboard.vue';
import MedicinesPage from './components/MedicinesPage.vue';
import SuppliersPage from './components/SuppliersPage.vue';
import CustomersPage from './components/CustomersPage.vue';
import PrescriptionsPage from './components/PrescriptionsPage.vue';
import StockPage from './components/StockPage.vue';
import SalesPage from './components/SalesPage.vue';

const user = ref(null);
const selectedTab = ref('dashboard');

const tabs = computed(() => {
  if (!user.value) return [];
  
  const allTabs = {
    admin: [
      { key: 'dashboard', label: 'Tableau de bord' },
      { key: 'medicines', label: 'Médicaments' },
      { key: 'suppliers', label: 'Fournisseurs' },
      { key: 'customers', label: 'Clients' },
    ],
    pharmacien: [
      { key: 'dashboard', label: 'Tableau de bord' },
      { key: 'prescriptions', label: 'Ordonnances' },
      { key: 'stock', label: 'Stock' },
      { key: 'sales', label: 'Ventes' },
      { key: 'medicines', label: 'Médicaments' },
    ],
    caissier: [
      { key: 'dashboard', label: 'Tableau de bord' },
      { key: 'sales', label: 'Ventes' },
      { key: 'customers', label: 'Clients' },
    ],
  };
  
  return allTabs[user.value.role] || allTabs.caissier;
});

const activeComponent = computed(() => {
  return {
    dashboard: Dashboard,
    medicines: MedicinesPage,
    suppliers: SuppliersPage,
    customers: CustomersPage,
    prescriptions: PrescriptionsPage,
    stock: StockPage,
    sales: SalesPage,
  }[selectedTab.value] ?? Dashboard;
});

function setToken(token) {
  if (token) {
    localStorage.setItem('pharmacie_token', token);
    window.axios.defaults.headers.common.Authorization = `Bearer ${token}`;
  }
}

function clearToken() {
  localStorage.removeItem('pharmacie_token');
  delete window.axios.defaults.headers.common.Authorization;
}

async function loadUser() {
  const token = localStorage.getItem('pharmacie_token');
  if (!token) {
    return;
  }

  setToken(token);

  try {
    const response = await window.axios.get('/api/auth/user');
    user.value = response.data;
  } catch (error) {
    clearToken();
    user.value = null;
  }
}

function handleAuthenticated(payload) {
  setToken(payload.token);
  user.value = payload.user;
}

async function logout() {
  try {
    await window.axios.post('/api/auth/logout');
  } finally {
    clearToken();
    user.value = null;
  }
}

onMounted(loadUser);
</script>
