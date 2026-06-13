<template>
  <!-- Page de connexion -->
  <div v-if="!user" class="min-h-screen">
    <LoginForm @authenticated="handleAuthenticated" />
  </div>

  <!-- App principale avec sidebar -->
  <div v-else class="flex min-h-screen bg-slate-100">
    <!-- Sidebar -->
    <aside class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col" style="background: #0f172a;">
      <!-- Logo -->
      <div class="flex h-16 items-center gap-3 px-5 border-b border-white/10">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-600 shadow-lg shadow-indigo-900/40">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
          </svg>
        </div>
        <div>
          <p class="text-sm font-bold text-white">PharmaGest</p>
          <p class="text-xs text-slate-500">Pharmacie Intelligente</p>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-slate-600">Menu</p>
        <button
          v-for="tab in tabs"
          :key="tab.key"
          @click="selectedTab = tab.key"
          :class="['sidebar-link w-full text-left', selectedTab === tab.key ? 'active' : '']"
        >
          <span v-html="tab.icon" class="flex-shrink-0"></span>
          {{ tab.label }}
        </button>
      </nav>

      <!-- User info + Logout -->
      <div class="border-t border-white/10 p-4">
        <div class="flex items-center gap-3 mb-3">
          <div class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo-600/30 text-indigo-400 text-sm font-bold">
            {{ user.name.charAt(0).toUpperCase() }}
          </div>
          <div class="min-w-0">
            <p class="truncate text-sm font-semibold text-white">{{ user.name }}</p>
            <p class="text-xs text-slate-500 capitalize">{{ roleLabel }}</p>
          </div>
        </div>
        <button
          @click="logout"
          class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-sm text-slate-400 transition hover:bg-white/10 hover:text-white"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          Se déconnecter
        </button>
      </div>
    </aside>

    <!-- Main content -->
    <div class="flex-1 pl-64">
      <!-- Top bar -->
      <header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-slate-200 bg-white/80 px-6 backdrop-blur-md">
        <div>
          <h1 class="text-base font-semibold text-slate-900">{{ activeTab?.label }}</h1>
          <p class="text-xs text-slate-500">{{ activeTab?.subtitle }}</p>
        </div>
        <div class="flex items-center gap-3">
          <!-- Alertes badge -->
          <div v-if="alertCount > 0" class="flex items-center gap-1.5 rounded-full bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 ring-1 ring-red-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            {{ alertCount }} alerte{{ alertCount > 1 ? 's' : '' }}
          </div>
        </div>
      </header>

      <!-- Page content -->
      <main class="p-6">
        <Transition name="page" mode="out-in">
          <component :is="activeComponent" :key="selectedTab" :user="user" @alerts-loaded="handleAlerts" />
        </Transition>
      </main>
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
const alertCount = ref(0);

const iconDashboard = `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>`;
const iconMedicines = `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>`;
const iconSuppliers = `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>`;
const iconCustomers = `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>`;
const iconPrescriptions = `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>`;
const iconStock = `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>`;
const iconSales = `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>`;

const allTabs = {
  admin: [
    { key: 'dashboard', label: 'Tableau de bord', subtitle: 'Vue d\'ensemble de la pharmacie', icon: iconDashboard },
    { key: 'medicines', label: 'Médicaments', subtitle: 'Gérer le catalogue', icon: iconMedicines },
    { key: 'suppliers', label: 'Fournisseurs', subtitle: 'Gérer les fournisseurs', icon: iconSuppliers },
    { key: 'customers', label: 'Clients', subtitle: 'Gérer les clients', icon: iconCustomers },
  ],
  pharmacien: [
    { key: 'dashboard', label: 'Tableau de bord', subtitle: 'Vue d\'ensemble de la pharmacie', icon: iconDashboard },
    { key: 'prescriptions', label: 'Ordonnances', subtitle: 'Gérer les ordonnances', icon: iconPrescriptions },
    { key: 'stock', label: 'Stock', subtitle: 'Gérer les stocks', icon: iconStock },
    { key: 'sales', label: 'Ventes', subtitle: 'Enregistrer des ventes', icon: iconSales },
    { key: 'medicines', label: 'Médicaments', subtitle: 'Gérer le catalogue', icon: iconMedicines },
  ],
  caissier: [
    { key: 'dashboard', label: 'Tableau de bord', subtitle: 'Vue d\'ensemble', icon: iconDashboard },
    { key: 'sales', label: 'Ventes', subtitle: 'Enregistrer des ventes', icon: iconSales },
    { key: 'customers', label: 'Clients', subtitle: 'Gérer les clients', icon: iconCustomers },
  ],
};

const tabs = computed(() => {
  if (!user.value) return [];
  return allTabs[user.value.role] || allTabs.caissier;
});

const activeTab = computed(() => tabs.value.find(t => t.key === selectedTab.value));

const roleLabel = computed(() => {
  const map = { admin: 'Administrateur', pharmacien: 'Pharmacien', caissier: 'Caissier' };
  return map[user.value?.role] || user.value?.role;
});

const activeComponent = computed(() => ({
  dashboard: Dashboard,
  medicines: MedicinesPage,
  suppliers: SuppliersPage,
  customers: CustomersPage,
  prescriptions: PrescriptionsPage,
  stock: StockPage,
  sales: SalesPage,
}[selectedTab.value] ?? Dashboard));

function handleAlerts(count) {
  alertCount.value = count;
}

function setToken(token) {
  if (token) {
    window.axios.defaults.headers.common.Authorization = `Bearer ${token}`;
  }
}

function clearToken() {
  delete window.axios.defaults.headers.common.Authorization;
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
    alertCount.value = 0;
  }
}

onMounted(() => {});
</script>

<style>
.page-enter-active,
.page-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.page-enter-from {
  opacity: 0;
  transform: translateY(8px);
}
.page-leave-to {
  opacity: 0;
  transform: translateY(-8px);
}
</style>
