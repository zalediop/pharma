<template>
  <div class="space-y-6 animate-fade-in">
    <!-- KPI Cards -->
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <div v-for="kpi in kpis" :key="kpi.label"
           class="card p-5 flex items-center gap-4 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
        <div :class="['flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-2xl', kpi.bg]">
          <span v-html="kpi.icon" :class="['[&>svg]:h-6 [&>svg]:w-6', kpi.iconColor]"></span>
        </div>
        <div>
          <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ kpi.label }}</p>
          <p class="mt-1 text-2xl font-bold text-slate-900">{{ kpi.value }}</p>
          <p v-if="kpi.sub" class="text-xs text-slate-400 mt-0.5">{{ kpi.sub }}</p>
        </div>
      </div>
    </div>

    <!-- Contenu principal -->
    <div class="grid gap-6 lg:grid-cols-2">
      <!-- Top médicaments -->
      <div class="card">
        <div class="card-header flex items-center justify-between">
          <div>
            <h3 class="font-semibold text-slate-900">Top médicaments vendus</h3>
            <p class="text-xs text-slate-400 mt-0.5">Classement par quantité vendue</p>
          </div>
          <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
          </div>
        </div>
        <div class="card-body pt-0">
          <div v-if="stats.top_medicines?.length" class="space-y-3">
            <div v-for="(item, index) in stats.top_medicines" :key="item.id" class="flex items-center gap-3">
              <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-500">
                {{ index + 1 }}
              </span>
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-1">
                  <span class="text-sm font-medium text-slate-800 truncate">{{ item.name }}</span>
                  <span class="text-sm font-bold text-slate-900 ml-2">{{ item.total_sold }}</span>
                </div>
                <div class="h-1.5 w-full rounded-full bg-slate-100">
                  <div
                    class="h-1.5 rounded-full bg-indigo-500"
                    :style="`width: ${Math.min(100, (item.total_sold / (stats.top_medicines[0]?.total_sold || 1)) * 100)}%`"
                  ></div>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="flex flex-col items-center justify-center py-8 text-center">
            <div class="h-12 w-12 rounded-2xl bg-slate-100 flex items-center justify-center mb-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
            <p class="text-sm text-slate-400">Aucune donnée disponible</p>
          </div>
        </div>
      </div>

      <!-- Alertes -->
      <div class="card">
        <div class="card-header flex items-center justify-between">
          <div>
            <h3 class="font-semibold text-slate-900">Alertes actives</h3>
            <p class="text-xs text-slate-400 mt-0.5">Stock faible et expirations proches</p>
          </div>
          <span v-if="alerts.length" class="badge-red">{{ alerts.length }}</span>
        </div>
        <div class="card-body pt-0">
          <div v-if="alerts.length" class="space-y-2">
            <div
              v-for="alert in alerts"
              :key="alert.id"
              class="flex items-start gap-3 rounded-xl bg-amber-50 p-3 ring-1 ring-amber-100"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0 text-amber-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
              <p class="text-sm text-amber-900">{{ alert.message }}</p>
            </div>
          </div>
          <div v-else class="flex flex-col items-center justify-center py-8 text-center">
            <div class="h-12 w-12 rounded-2xl bg-emerald-50 flex items-center justify-center mb-3">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <p class="text-sm font-medium text-emerald-700">Tout est en ordre !</p>
            <p class="text-xs text-slate-400 mt-1">Aucune alerte active</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const emit = defineEmits(['alerts-loaded']);

const stats = ref({ today_sales: 0, week_sales: 0, month_sales: 0, revenue: 0, expiring_soon: 0, top_medicines: [] });
const alerts = ref([]);

function formatCFA(value) {
  const amount = Number(value ?? 0);
  return amount.toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + ' FCFA';
}

const kpis = computed(() => [
  {
    label: 'Ventes aujourd\'hui',
    value: stats.value.today_sales,
    bg: 'bg-indigo-50',
    iconColor: 'text-indigo-600',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>`,
  },
  {
    label: 'Ventes ce mois',
    value: stats.value.month_sales,
    bg: 'bg-blue-50',
    iconColor: 'text-blue-600',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>`,
  },
  {
    label: 'Chiffre d\'affaires',
    value: formatCFA(stats.value.revenue),
    bg: 'bg-emerald-50',
    iconColor: 'text-emerald-600',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`,
  },
  {
    label: 'Produits périmant bientôt',
    value: stats.value.expiring_soon,
    bg: 'bg-amber-50',
    iconColor: 'text-amber-600',
    sub: 'Dans les 30 prochains jours',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>`,
  },
]);

async function loadDashboard() {
  try {
    const response = await window.axios.get('/api/dashboard');
    stats.value = response.data;
  } catch {
    stats.value = { today_sales: 0, week_sales: 0, month_sales: 0, revenue: 0, expiring_soon: 0, top_medicines: [] };
  }

  try {
    const response = await window.axios.get('/api/alerts');
    alerts.value = response.data;
    emit('alerts-loaded', alerts.value.length);
  } catch {
    alerts.value = [];
  }
}

onMounted(loadDashboard);
</script>
