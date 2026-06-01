<template>
  <div class="space-y-6">
    <section class="rounded-xl bg-white p-6 shadow-sm">
      <h2 class="text-xl font-semibold">Tableau de bord</h2>
      <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
          <p class="text-sm text-slate-500">Ventes aujourd'hui</p>
          <p class="mt-2 text-3xl font-semibold">{{ stats.today_sales }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
          <p class="text-sm text-slate-500">Chiffre d'affaires</p>
          <p class="mt-2 text-3xl font-semibold">{{ formatCFA(stats.revenue) }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
          <p class="text-sm text-slate-500">Produits bientôt périmés</p>
          <p class="mt-2 text-3xl font-semibold">{{ stats.expiring_soon }}</p>
        </div>
      </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-2">
      <div class="rounded-xl bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold">Principaux médicaments vendus</h3>
        <ul class="mt-4 space-y-2">
          <li v-for="item in stats.top_medicines" :key="item.id" class="rounded border border-slate-200 p-3">
            <div class="flex items-center justify-between gap-4">
              <span>{{ item.name }}</span>
              <strong>{{ item.total_sold }}</strong>
            </div>
          </li>
          <li v-if="!stats.top_medicines.length" class="text-slate-500">Aucune donnée disponible.</li>
        </ul>
      </div>

      <div class="rounded-xl bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold">Alertes</h3>
        <div v-if="alerts.length" class="mt-4 space-y-3">
          <div v-for="alert in alerts" :key="alert.id" class="rounded border border-amber-200 bg-amber-50 p-3">
            <p class="font-medium">{{ alert.message }}</p>
          </div>
        </div>
        <p v-else class="mt-4 text-slate-500">Aucune alerte active.</p>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const stats = ref({ today_sales: 0, revenue: 0, expiring_soon: 0, top_medicines: [] });
const alerts = ref([]);

function formatCFA(value) {
  const amount = Number(value ?? 0);
  return amount.toLocaleString('fr-FR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }) + ' FCFA';
}

async function loadDashboard() {
  try {
    const response = await window.axios.get('/api/dashboard');
    stats.value = response.data;
  } catch (error) {
    stats.value = { today_sales: 0, revenue: 0, expiring_soon: 0, top_medicines: [] };
  }

  try {
    const response = await window.axios.get('/api/alerts');
    alerts.value = response.data;
  } catch (error) {
    alerts.value = [];
  }
}

onMounted(loadDashboard);
</script>
