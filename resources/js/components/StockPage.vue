<template>
  <div class="space-y-6">
    <section class="rounded-xl bg-white p-6 shadow-sm">
      <h2 class="text-xl font-semibold">Gestion du stock</h2>
      <div class="mt-4 flex flex-wrap gap-2">
        <input v-model="search" type="search" placeholder="Rechercher un médicament" class="rounded border border-slate-300 px-3 py-2" />
        <button @click="loadStock" class="rounded bg-sky-600 px-4 py-2 text-white hover:bg-sky-700">Rechercher</button>
      </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-[1fr_1fr]">
      <section class="rounded-xl bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold mb-4">Stock disponible</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50">
              <tr>
                <th class="px-4 py-3">Médicament</th>
                <th class="px-4 py-3">Quantity</th>
                <th class="px-4 py-3">Expiration</th>
                <th class="px-4 py-3">Statut</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              <tr v-for="item in stock.data" :key="item.id" :class="{'bg-red-50': item.quantity < 10}">
                <td class="px-4 py-3">{{ item.medicine?.name }}</td>
                <td class="px-4 py-3">
                  <span :class="item.quantity < 10 ? 'font-semibold text-red-600' : ''">{{ item.quantity }}</span>
                </td>
                <td class="px-4 py-3">{{ new Date(item.expiry_date).toLocaleDateString('fr-FR') }}</td>
                <td class="px-4 py-3">
                  <span v-if="item.quantity < 10" class="rounded bg-red-100 px-2 py-1 text-xs font-semibold text-red-800">Stock faible</span>
                  <span v-else-if="isExpiringSoon(item.expiry_date)" class="rounded bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-800">Expiration proche</span>
                  <span v-else class="rounded bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-800">OK</span>
                </td>
              </tr>
              <tr v-if="!stock.data.length">
                <td colspan="4" class="px-4 py-4 text-center text-slate-500">Aucun stock trouvé.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section class="rounded-xl bg-white p-6 shadow-sm space-y-6">
        <div>
          <h3 class="text-lg font-semibold mb-4">Alertes stock</h3>
          <div class="space-y-3">
            <div v-for="alert in alerts" :key="alert.id" class="rounded border border-amber-200 bg-amber-50 p-3">
              <p class="font-medium text-amber-900">{{ alert.message }}</p>
            </div>
            <p v-if="!alerts.length" class="text-slate-500">Aucune alerte active.</p>
          </div>
        </div>

        <div>
          <h3 class="text-lg font-semibold mb-4">Ajouter au stock</h3>
          <form @submit.prevent="addStock" class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-slate-700">Médicament</label>
              <select v-model="newStockForm.medicine_id" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2">
                <option value="">Sélectionner...</option>
                <option v-for="med in medicines" :key="med.id" :value="med.id">{{ med.name }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700">Quantité</label>
              <input v-model="newStockForm.quantity" type="number" required min="1" class="mt-1 w-full rounded border border-slate-300 px-3 py-2" />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700">Date d'expiration</label>
              <input v-model="newStockForm.expiry_date" type="date" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2" />
            </div>
            <button type="submit" class="rounded bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">Ajouter</button>
            <p v-if="message" class="text-sm text-emerald-700">{{ message }}</p>
          </form>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';

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
  message.value = 'Stock ajouté.';
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
