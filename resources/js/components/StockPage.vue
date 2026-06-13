<template>
  <div class="space-y-6 animate-fade-in">
    <!-- En-tête page -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-lg font-semibold text-slate-900">Gestion du stock</h2>
        <p class="text-sm text-slate-500">Suivi des quantités et dates d'expiration</p>
      </div>
      <div class="flex items-center gap-2">
        <div class="relative">
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
          <input
            v-model="search"
            @keyup.enter="loadStock"
            type="search"
            placeholder="Rechercher..."
            class="input-field pl-9 w-56"
          />
        </div>
        <button @click="loadStock" class="btn-secondary">Chercher</button>
      </div>
    </div>

    <!-- Contenu principal -->
    <div class="grid gap-6 lg:grid-cols-[1fr_380px]">
      <!-- Table -->
      <div class="card overflow-hidden">
        <div class="card-header">
          <h3 class="font-semibold text-slate-900">Stock disponible</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="data-table">
            <thead>
              <tr>
                <th>Médicament</th>
                <th>Quantité restante</th>
                <th>Date d'expiration</th>
                <th class="text-right pr-4">Statut</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in stock.data" :key="item.id" :class="{'bg-red-50/50 hover:bg-red-50': item.quantity < 10}">
                <td class="font-medium text-slate-900">{{ item.medicine?.name }}</td>
                <td>
                  <span :class="['font-mono font-bold', item.quantity < 10 ? 'text-red-600' : 'text-slate-700']">
                      {{ item.quantity }}
                  </span>
                </td>
                <td>
                    <span :class="['text-sm', isExpiringSoon(item.expiry_date) ? 'text-amber-600 font-medium' : 'text-slate-600']">
                        {{ new Date(item.expiry_date).toLocaleDateString('fr-FR') }}
                    </span>
                </td>
                <td class="text-right pr-4">
                  <span v-if="item.quantity < 10" class="badge-red">Stock faible</span>
                  <span v-else-if="isExpiringSoon(item.expiry_date)" class="badge-amber">Expiration proche</span>
                  <span v-else class="badge-green">OK</span>
                </td>
              </tr>
              <tr v-if="!stock.data?.length">
                <td colspan="4" class="py-12 text-center">
                  <div class="flex flex-col items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <p class="text-sm text-slate-400">Aucun stock trouvé</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Sidebar droite -->
      <div class="space-y-6">
        <!-- Alertes -->
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h3 class="font-semibold text-slate-900">Alertes stock</h3>
                <span v-if="alerts.length" class="flex h-5 w-5 items-center justify-center rounded-full bg-red-100 text-[10px] font-bold text-red-600">{{ alerts.length }}</span>
            </div>
            <div class="card-body">
                <div v-if="alerts.length" class="space-y-2">
                    <div v-for="alert in alerts" :key="alert.id" class="flex items-start gap-3 rounded-xl bg-amber-50 p-3 ring-1 ring-amber-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0 text-amber-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <p class="text-sm text-amber-900">{{ alert.message }}</p>
                    </div>
                </div>
                <div v-else class="flex flex-col items-center justify-center py-6 text-center">
                    <div class="h-10 w-10 rounded-full bg-emerald-50 flex items-center justify-center mb-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <p class="text-sm text-slate-500">Aucune alerte active</p>
                </div>
            </div>
        </div>

        <!-- Formulaire d'ajout -->
        <div class="card">
          <div class="card-header">
            <h3 class="font-semibold text-slate-900">Entrée en stock</h3>
            <p class="text-xs text-slate-400 mt-0.5">Ajouter un nouveau lot</p>
          </div>
          <div class="card-body">
            <form @submit.prevent="addStock" class="space-y-4">
              <div>
                <label class="input-label">Médicament</label>
                <select v-model="newStockForm.medicine_id" required class="input-field">
                  <option value="">Sélectionner...</option>
                  <option v-for="med in medicines" :key="med.id" :value="med.id">{{ med.name }}</option>
                </select>
              </div>
              <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="input-label">Quantité</label>
                    <input v-model="newStockForm.quantity" type="number" required min="1" class="input-field" placeholder="ex: 100" />
                  </div>
                  <div>
                    <label class="input-label">Date d'expiration</label>
                    <input v-model="newStockForm.expiry_date" type="date" required class="input-field" />
                  </div>
              </div>

              <!-- Toast succès -->
              <div v-if="message" class="flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700 ring-1 ring-emerald-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ message }}
              </div>

              <div class="pt-1">
                <button type="submit" class="btn-primary w-full justify-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                  Ajouter au stock
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';

const emit = defineEmits(['alerts-loaded']);

const stock = ref({ data: [] });
const medicines = ref([]);
const search = ref('');
const alerts = ref([]);
const newStockForm = reactive({ medicine_id: '', quantity: '', expiry_date: '' });
const message = ref('');

async function loadStock() {
  try {
    const response = await window.axios.get('/api/stocks', { params: { search: search.value } });
    stock.value = response.data;
  } catch {
    stock.value = { data: [] };
  }
}

async function loadMedicines() {
  const response = await window.axios.get('/api/medicines?limit=1000');
  medicines.value = response.data.data || [];
}

async function loadAlerts() {
  try {
    const response = await window.axios.get('/api/alerts');
    alerts.value = response.data;
    emit('alerts-loaded', alerts.value.length);
  } catch {
    alerts.value = [];
  }
}

function isExpiringSoon(expiryDate) {
  const today = new Date();
  const expiry = new Date(expiryDate);
  const daysUntilExpiry = Math.floor((expiry - today) / (1000 * 60 * 60 * 24));
  return daysUntilExpiry <= 30 && daysUntilExpiry > 0;
}

async function addStock() {
  const payload = {
    medicine_id: newStockForm.medicine_id,
    quantity: newStockForm.quantity,
    expiry_date: newStockForm.expiry_date,
    alert_threshold: 10,
  };
  await window.axios.post('/api/stocks', payload);
  message.value = 'Stock ajouté avec succès.';
  Object.assign(newStockForm, { medicine_id: '', quantity: '', expiry_date: '' });
  await loadStock();
  await loadAlerts();
  setTimeout(() => { message.value = ''; }, 3000);
}

onMounted(() => {
  loadStock();
  loadMedicines();
  loadAlerts();
});
</script>
