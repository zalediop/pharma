<template>
  <div class="space-y-6">
    <section class="rounded-xl bg-white p-6 shadow-sm">
      <h2 class="text-xl font-semibold">Ventes</h2>
      <p class="text-slate-600 mt-2">Enregistrer une vente de médicaments.</p>
    </section>

    <div class="grid gap-6 lg:grid-cols-[2fr_1fr]">
      <section class="rounded-xl bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold mb-4">Historique des ventes</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50">
              <tr>
                <th class="px-4 py-3">Client</th>
                <th class="px-4 py-3">Médicaments</th>
                <th class="px-4 py-3">Montant</th>
                <th class="px-4 py-3">Date</th>
                <th class="px-4 py-3">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              <tr v-for="sale in sales.data" :key="sale.id">
                <td class="px-4 py-3">{{ sale.customer?.name || '-' }}</td>
                <td class="px-4 py-3">{{ sale.items?.length || 0 }} article(s)</td>
                <td class="px-4 py-3">{{ formatCFA(sale.total_amount) }}</td>
                <td class="px-4 py-3">{{ new Date(sale.created_at).toLocaleDateString('fr-FR') }}</td>
                <td class="px-4 py-3">
                  <button @click="viewSale(sale)" class="rounded bg-sky-500 px-3 py-1 text-white hover:bg-sky-600">Voir</button>
                </td>
              </tr>
              <tr v-if="!sales.data.length">
                <td colspan="5" class="px-4 py-4 text-center text-slate-500">Aucune vente enregistrée.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section class="rounded-xl bg-white p-6 shadow-sm space-y-4">
        <h3 class="text-lg font-semibold">Nouvelle vente</h3>
        <form @submit.prevent="createSale" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-700">Client</label>
            <select v-model="form.customer_id" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2">
              <option value="">Sélectionner...</option>
              <option v-for="customer in customers" :key="customer.id" :value="customer.id">{{ customer.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Médicament</label>
            <select v-model="currentItem.medicine_id" class="mt-1 w-full rounded border border-slate-300 px-3 py-2">
              <option value="">Sélectionner...</option>
              <option v-for="med in medicines" :key="med.id" :value="med.id">{{ med.name }}</option>
            </select>
          </div>
          <div class="grid grid-cols-2 gap-2">
            <div>
              <label class="block text-sm font-medium text-slate-700">Qty</label>
              <input v-model="currentItem.quantity" type="number" min="1" class="mt-1 w-full rounded border border-slate-300 px-3 py-2" />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700">Prix</label>
              <input v-model="currentItem.unit_price" type="number" min="0" step="0.01" class="mt-1 w-full rounded border border-slate-300 px-3 py-2" />
            </div>
          </div>
          <button type="button" @click="addItem" class="w-full rounded bg-sky-500 px-3 py-2 text-white hover:bg-sky-600">Ajouter article</button>

          <div v-if="form.items.length" class="border-t pt-4">
            <p class="mb-2 font-semibold">Articles:</p>
            <ul class="space-y-1 text-sm">
              <li v-for="(item, idx) in form.items" :key="idx" class="flex justify-between">
                <span>{{ getMedicineName(item.medicine_id) }} x {{ item.quantity }}</span>
                <button type="button" @click="removeItem(idx)" class="text-red-600 hover:text-red-800">×</button>
              </li>
            </ul>
          </div>

          <button type="submit" :disabled="!form.customer_id || !form.items.length" class="w-full rounded bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700 disabled:bg-slate-300">Valider vente</button>
          <p v-if="message" class="text-sm text-emerald-700">{{ message }}</p>
        </form>
      </section>
    </div>

    <div v-if="selectedSale" class="rounded-xl bg-white p-6 shadow-sm">
      <h3 class="text-lg font-semibold mb-4">Détails de la vente</h3>
      <div class="grid gap-4 lg:grid-cols-2">
        <div>
          <label class="block text-xs font-medium text-slate-600">Client</label>
          <p class="text-sm font-semibold">{{ selectedSale.customer?.name }}</p>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600">Date</label>
          <p class="text-sm font-semibold">{{ new Date(selectedSale.created_at).toLocaleDateString('fr-FR') }}</p>
        </div>
      </div>
      <div class="mt-4">
        <label class="block text-xs font-medium text-slate-600">Médicaments</label>
        <table class="mt-2 w-full text-sm">
          <thead class="border-b border-slate-200">
            <tr>
              <th class="text-left">Nom</th>
              <th class="text-right">Qty</th>
              <th class="text-right">Prix</th>
              <th class="text-right">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in selectedSale.items" :key="item.id">
              <td>{{ item.medicine?.name || 'N/A' }}</td>
              <td class="text-right">{{ item.quantity || 0 }}</td>
              <td class="text-right">{{ formatCFA(item.unit_price) }}</td>
              <td class="text-right font-semibold">{{ formatCFA((item.quantity || 0) * (item.unit_price || 0)) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="mt-4 border-t border-slate-200 pt-4 text-right">
        <p class="text-lg font-semibold">Total: {{ formatCFA(selectedSale.total_amount) }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';

const sales = ref({ data: [] });
const customers = ref([]);
const medicines = ref([]);
const selectedSale = ref(null);
const message = ref('');

const form = reactive({
  customer_id: '',
  items: [],
});

const currentItem = reactive({
  medicine_id: '',
  quantity: '',
  unit_price: '',
});

function formatCFA(value) {
  const amount = Number(value ?? 0);
  return amount.toLocaleString('fr-FR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }) + ' FCFA';
}

function getMedicineName(medicineId) {
  const med = medicines.value.find((m) => m.id === parseInt(medicineId));
  return med?.name || 'Inconnu';
}

function addItem() {
  if (!currentItem.medicine_id || !currentItem.quantity || !currentItem.unit_price) {
    return;
  }
  form.items.push({
    medicine_id: parseInt(currentItem.medicine_id),
    quantity: parseInt(currentItem.quantity),
    unit_price: parseFloat(currentItem.unit_price),
  });
  Object.assign(currentItem, { medicine_id: '', quantity: '', unit_price: '' });
}

function removeItem(idx) {
  form.items.splice(idx, 1);
}

async function loadSales() {
  try {
    const response = await window.axios.get('/api/sales');
    sales.value = response.data;
  } catch {
    sales.value = { data: [] };
  }
}

async function loadCustomers() {
  try {
    const response = await window.axios.get('/api/customers?limit=1000');
    customers.value = response.data.data || [];
  } catch {
    customers.value = [];
  }
}

async function loadMedicines() {
  try {
    const response = await window.axios.get('/api/medicines?limit=1000');
    medicines.value = response.data.data || [];
  } catch {
    medicines.value = [];
  }
}

function viewSale(sale) {
  selectedSale.value = sale;
}

async function createSale() {
  try {
    const response = await window.axios.post('/api/sales', form);
    message.value = 'Vente enregistrée.';
    Object.assign(form, { customer_id: '', items: [] });
    await loadSales();
    setTimeout(() => {
      message.value = '';
    }, 3000);
  } catch (error) {
    message.value = error.response?.data?.message || 'Erreur lors de l\'enregistrement.';
  }
}

onMounted(() => {
  loadSales();
  loadCustomers();
  loadMedicines();
});
</script>
