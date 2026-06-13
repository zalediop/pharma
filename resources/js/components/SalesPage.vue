<template>
  <div class="space-y-6 animate-fade-in">
    <!-- En-tête page -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-lg font-semibold text-slate-900">Ventes</h2>
        <p class="text-sm text-slate-500">{{ sales.total ?? sales.data?.length ?? 0 }} vente(s) enregistrée(s)</p>
      </div>
      <!-- Optionnel: Ajouter un filtre de date ici si nécessaire -->
    </div>

    <!-- Contenu principal -->
    <div class="grid gap-6 xl:grid-cols-[2fr_1.5fr]">
      <!-- Table historique -->
      <div class="card overflow-hidden">
        <div class="card-header">
          <h3 class="font-semibold text-slate-900">Historique des ventes</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="data-table">
            <thead>
              <tr>
                <th>Date & Heure</th>
                <th>Client</th>
                <th>Montant Total</th>
                <th class="text-right pr-4">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="sale in sales.data" :key="sale.id">
                <td class="font-medium text-slate-900">
                    {{ new Date(sale.created_at).toLocaleDateString('fr-FR') }}
                    <span class="text-xs text-slate-500 font-normal ml-1">{{ new Date(sale.created_at).toLocaleTimeString('fr-FR', {hour: '2-digit', minute:'2-digit'}) }}</span>
                </td>
                <td>
                    <div class="flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-600">
                            {{ sale.customer?.name ? sale.customer.name.charAt(0).toUpperCase() : '?' }}
                        </span>
                        <span class="text-slate-700">{{ sale.customer?.name || 'Client Inconnu' }}</span>
                    </div>
                </td>
                <td class="font-semibold text-slate-900">{{ formatCFA(sale.total_amount) }}</td>
                <td class="text-right pr-4">
                  <button @click="viewSale(sale)" class="btn-secondary btn-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Détails
                  </button>
                </td>
              </tr>
              <tr v-if="!sales.data?.length">
                <td colspan="4" class="py-12 text-center">
                  <div class="flex flex-col items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p class="text-sm text-slate-400">Aucune vente trouvée</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Colonne Droite: Nouvelle Vente ou Détails -->
      <div class="space-y-6">

        <!-- Nouvelle Vente -->
        <div v-if="!selectedSale" class="card">
          <div class="card-header bg-slate-50/50">
            <h3 class="font-semibold text-slate-900 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nouvelle vente
            </h3>
          </div>
          <div class="card-body">
            <form @submit.prevent="createSale" class="space-y-5">
              <!-- Client -->
              <div>
                <label class="input-label">Client</label>
                <select v-model="form.customer_id" required class="input-field mt-1">
                  <option value="">Sélectionner un client...</option>
                  <option v-for="customer in customers" :key="customer.id" :value="customer.id">{{ customer.name }}</option>
                </select>
              </div>

              <!-- Ajout article -->
              <div class="rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200">
                  <p class="text-sm font-semibold text-slate-700 mb-3">Ajouter un article</p>
                  <div class="space-y-3">
                      <div>
                        <label class="text-xs font-medium text-slate-600">Médicament</label>
                        <select v-model="currentItem.medicine_id" class="input-field mt-1 py-2">
                          <option value="">Sélectionner...</option>
                          <option v-for="med in medicines" :key="med.id" :value="med.id">{{ med.name }}</option>
                        </select>
                      </div>
                      <div class="grid grid-cols-2 gap-3">
                        <div>
                          <label class="text-xs font-medium text-slate-600">Quantité</label>
                          <input v-model="currentItem.quantity" type="number" min="1" class="input-field mt-1 py-2" placeholder="ex: 2" />
                        </div>
                        <div>
                          <label class="text-xs font-medium text-slate-600">Prix unitaire (CFA)</label>
                          <input v-model="currentItem.unit_price" type="number" min="0" step="0.01" class="input-field mt-1 py-2" placeholder="ex: 1500" />
                        </div>
                      </div>
                      <button type="button" @click="addItem" class="btn-secondary w-full justify-center mt-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Ajouter
                      </button>
                  </div>
              </div>

              <!-- Liste articles -->
              <div v-if="form.items.length" class="space-y-3 pt-2">
                <p class="text-sm font-semibold text-slate-700 flex justify-between items-end border-b border-slate-100 pb-2">
                    Panier
                    <span class="text-xs font-normal text-slate-500">{{ form.items.length }} article(s)</span>
                </p>
                <ul class="space-y-2">
                  <li v-for="(item, idx) in form.items" :key="idx" class="flex items-center justify-between rounded-lg bg-white p-2 ring-1 ring-slate-200 shadow-sm">
                    <div class="flex-1 min-w-0 pr-3">
                        <p class="text-sm font-medium text-slate-800 truncate">{{ getMedicineName(item.medicine_id) }}</p>
                        <p class="text-xs text-slate-500">{{ item.quantity }} × {{ formatCFA(item.unit_price) }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-sm font-semibold text-slate-900">{{ formatCFA(item.quantity * item.unit_price) }}</span>
                        <button type="button" @click="removeItem(idx)" class="flex h-6 w-6 items-center justify-center rounded-md text-slate-400 hover:bg-red-50 hover:text-red-500 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                  </li>
                </ul>

                <!-- Total -->
                <div class="flex items-center justify-between rounded-xl bg-indigo-50 p-4 ring-1 ring-indigo-100 mt-4">
                    <span class="font-semibold text-indigo-900">Total à payer</span>
                    <span class="text-xl font-bold text-indigo-700">{{ formatCFA(cartTotal) }}</span>
                </div>
              </div>

              <!-- Toast succès/erreur -->
              <div v-if="message" :class="['flex items-center gap-2 rounded-xl px-4 py-3 text-sm ring-1', isError ? 'bg-red-50 text-red-700 ring-red-100' : 'bg-emerald-50 text-emerald-700 ring-emerald-100']">
                  <svg v-if="!isError" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                {{ message }}
              </div>

              <button type="submit" :disabled="!form.customer_id || !form.items.length" class="btn-success w-full justify-center py-3 text-base">
                Valider la vente
              </button>
            </form>
          </div>
        </div>

        <!-- Détails Vente -->
        <div v-else class="card border-t-4 border-t-indigo-500">
          <div class="card-header flex items-center justify-between bg-slate-50/50">
            <h3 class="font-semibold text-slate-900 flex items-center gap-2">
                Détails du reçu
            </h3>
            <button @click="selectedSale = null" class="text-slate-400 hover:text-slate-600 rounded-full p-1 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
          </div>
          <div class="card-body space-y-6">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div>
                    <p class="text-xs font-semibold uppercase text-slate-400 tracking-wider">Client</p>
                    <p class="font-medium text-slate-900 mt-1">{{ selectedSale.customer?.name || 'Inconnu' }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs font-semibold uppercase text-slate-400 tracking-wider">Date</p>
                    <p class="font-medium text-slate-900 mt-1">{{ new Date(selectedSale.created_at).toLocaleDateString('fr-FR') }}</p>
                </div>
            </div>

            <div>
              <p class="text-xs font-semibold uppercase text-slate-400 tracking-wider mb-3">Articles facturés</p>
              <div class="space-y-2">
                  <div v-for="item in selectedSale.items" :key="item.id" class="flex items-center justify-between py-2">
                      <div class="flex-1">
                          <p class="text-sm font-medium text-slate-800">{{ item.medicine?.name || 'N/A' }}</p>
                          <p class="text-xs text-slate-500">{{ item.quantity }} × {{ formatCFA(item.unit_price) }}</p>
                      </div>
                      <div class="font-semibold text-slate-900">
                          {{ formatCFA((item.quantity || 0) * (item.unit_price || 0)) }}
                      </div>
                  </div>
              </div>
            </div>

            <div class="flex items-center justify-between pt-4 border-t-2 border-dashed border-slate-200">
              <span class="text-sm font-semibold uppercase text-slate-500">Total payé</span>
              <span class="text-2xl font-bold text-indigo-600">{{ formatCFA(selectedSale.total_amount) }}</span>
            </div>
            
            <div class="pt-4 flex justify-center">
                 <button class="btn-secondary w-full justify-center">
                     <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                     </svg>
                     Imprimer le reçu
                 </button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted, computed } from 'vue';

const sales = ref({ data: [] });
const customers = ref([]);
const medicines = ref([]);
const selectedSale = ref(null);
const message = ref('');
const isError = ref(false);

const form = reactive({
  customer_id: '',
  items: [],
});

const currentItem = reactive({
  medicine_id: '',
  quantity: '',
  unit_price: '',
});

const cartTotal = computed(() => {
    return form.items.reduce((total, item) => total + (item.quantity * item.unit_price), 0);
});

function formatCFA(value) {
  const amount = Number(value ?? 0);
  return amount.toLocaleString('fr-FR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }) + ' CFA';
}

function getMedicineName(medicineId) {
  const med = medicines.value.find((m) => m.id === parseInt(medicineId));
  return med?.name || 'Inconnu';
}

function addItem() {
  if (!currentItem.medicine_id || !currentItem.quantity || !currentItem.unit_price) {
    return;
  }
  const qty = parseInt(currentItem.quantity, 10);
  const price = parseFloat(currentItem.unit_price);
  
  if (isNaN(qty) || isNaN(price) || qty <= 0 || price < 0) {
    return;
  }
  
  form.items.push({
    medicine_id: parseInt(currentItem.medicine_id, 10),
    quantity: qty,
    unit_price: price,
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
    isError.value = false;
    const response = await window.axios.post('/api/sales', form);
    message.value = 'Vente validée avec succès.';
    Object.assign(form, { customer_id: '', items: [] });
    await loadSales();
    setTimeout(() => { message.value = ''; }, 3000);
  } catch (error) {
    isError.value = true;
    message.value = error.response?.data?.message || 'Erreur lors de l\'enregistrement.';
    setTimeout(() => { message.value = ''; }, 5000);
  }
}

onMounted(() => {
  loadSales();
  loadCustomers();
  loadMedicines();
});
</script>
