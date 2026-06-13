<template>
  <div class="space-y-6 animate-fade-in">
    <!-- En-tête page -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-lg font-semibold text-slate-900">Clients</h2>
        <p class="text-sm text-slate-500">{{ customers.total ?? customers.data?.length ?? 0 }} client(s) enregistré(s)</p>
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
            @keyup.enter="loadCustomers"
            type="search"
            placeholder="Rechercher..."
            class="input-field pl-9 w-56"
          />
        </div>
        <button @click="loadCustomers" class="btn-secondary">Chercher</button>
      </div>
    </div>

    <div v-if="error" class="flex items-center gap-2 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700 ring-1 ring-red-100">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        {{ error }}
    </div>

    <!-- Contenu principal -->
    <div class="grid gap-6 lg:grid-cols-[1fr_380px]">
      <!-- Table -->
      <div class="card overflow-hidden">
        <div class="card-header">
          <h3 class="font-semibold text-slate-900">Liste des clients</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="data-table">
            <thead>
              <tr>
                <th>Nom</th>
                <th>Contact</th>
                <th>Médicaments chroniques</th>
                <th class="text-right pr-4">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="customer in customers.data" :key="customer.id">
                <td class="font-medium text-slate-900">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 font-bold text-xs">
                            {{ customer.name.charAt(0).toUpperCase() }}
                        </div>
                        {{ customer.name }}
                    </div>
                </td>
                <td>
                    <div class="text-sm">
                        <p v-if="customer.email" class="text-slate-600">{{ customer.email }}</p>
                        <p v-if="customer.phone" class="text-slate-500 text-xs">{{ customer.phone }}</p>
                        <span v-if="!customer.email && !customer.phone" class="text-slate-400 italic">-</span>
                    </div>
                </td>
                <td>
                  <div class="flex flex-wrap gap-1 max-w-[200px]">
                      <template v-if="customer.chronic_medication && customer.chronic_medication.length">
                          <span v-for="(med, idx) in (Array.isArray(customer.chronic_medication) ? customer.chronic_medication : [customer.chronic_medication])" :key="idx" class="badge-blue text-[10px] px-1.5 py-0.5">
                              {{ med }}
                          </span>
                      </template>
                      <span v-else class="text-slate-400 italic text-xs">-</span>
                  </div>
                </td>
                <td class="text-right pr-4">
                  <div class="flex items-center justify-end gap-2">
                    <button @click="editCustomer(customer)" class="btn-primary btn-sm">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                      </svg>
                      Modifier
                    </button>
                    <button @click="deleteCustomer(customer.id)" class="btn-danger btn-sm">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                      Supprimer
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!customers.data?.length">
                <td colspan="4" class="py-12 text-center">
                  <div class="flex flex-col items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p class="text-sm text-slate-400">Aucun client trouvé</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Formulaire -->
      <div class="card">
        <div class="card-header">
          <h3 class="font-semibold text-slate-900">
            {{ editingId ? 'Modifier le client' : 'Ajouter un client' }}
          </h3>
          <p class="text-xs text-slate-400 mt-0.5">
            {{ editingId ? 'Mettez à jour les coordonnées' : 'Créer un nouveau profil client' }}
          </p>
        </div>
        <div class="card-body">
          <form @submit.prevent="saveCustomer" class="space-y-4">
            <div>
              <label class="input-label">Nom complet</label>
              <input v-model="form.name" type="text" required class="input-field" placeholder="ex: Jean Dupont" />
            </div>
            <div>
              <label class="input-label">Email (optionnel)</label>
              <input v-model="form.email" type="email" class="input-field" placeholder="ex: jean@example.com" />
            </div>
            <div>
              <label class="input-label">Téléphone (optionnel)</label>
              <input v-model="form.phone" type="text" class="input-field" placeholder="ex: +221 77..." />
            </div>
            <div>
              <label class="input-label">Médicaments chroniques</label>
              <input v-model="form.chronic_medication" type="text" class="input-field" placeholder="Séparés par des virgules" />
            </div>

            <!-- Toast succès -->
            <div v-if="message" class="flex items-center gap-2 rounded-xl bg-emerald-50 px-4 py-3 text-sm text-emerald-700 ring-1 ring-emerald-100">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              {{ message }}
            </div>

            <div class="flex gap-2 pt-1">
              <button type="submit" class="btn-success flex-1 justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ editingId ? 'Enregistrer' : 'Ajouter' }}
              </button>
              <button v-if="editingId" type="button" @click="cancelEdit" class="btn-secondary">
                Annuler
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';

const customers = ref({ data: [] });
const search = ref('');
const editingId = ref(null);
const form = reactive({ name: '', email: '', phone: '', chronic_medication: '' });
const message = ref('');
const error = ref('');

async function loadCustomers() {
  error.value = '';
  try {
    const response = await window.axios.get('/api/customers', { params: { search: search.value } });
    const payload = response.data;
    if (Array.isArray(payload)) {
      customers.value = { data: payload };
      return;
    }
    customers.value = payload.data ? payload : { data: payload };
  } catch (err) {
    error.value = err.response?.data?.message || 'Impossible de charger les clients.';
    customers.value = { data: [] };
  }
}

function resetForm() {
  editingId.value = null;
  Object.assign(form, { name: '', email: '', phone: '', chronic_medication: '' });
  message.value = '';
}

function editCustomer(customer) {
  editingId.value = customer.id;
  Object.assign(form, {
    name: customer.name,
    email: customer.email || '',
    phone: customer.phone || '',
    chronic_medication: formatChronicMedication(customer.chronic_medication),
  });
  message.value = '';
}

async function saveCustomer() {
  const payload = {
    name: form.name,
    email: form.email || null,
    phone: form.phone || null,
    chronic_medication: form.chronic_medication ? form.chronic_medication.split(',').map(item => item.trim()) : [],
  };

  if (editingId.value) {
    const response = await window.axios.patch(`/api/customers/${editingId.value}`, payload);
    customers.value.data = customers.value.data.map(c => c.id === editingId.value ? response.data : c);
    message.value = 'Client modifié avec succès.';
    setTimeout(() => resetForm(), 2000);
    return;
  }

  const response = await window.axios.post('/api/customers', payload);
  customers.value.data.unshift(response.data);
  message.value = 'Client ajouté avec succès.';
  setTimeout(() => resetForm(), 2000);
}

async function deleteCustomer(id) {
  if (!confirm('Voulez-vous vraiment supprimer ce client ?')) return;
  await window.axios.delete(`/api/customers/${id}`);
  await loadCustomers();
}

function cancelEdit() { resetForm(); }

function formatChronicMedication(value) {
  if (!value) return '';
  return Array.isArray(value) ? value.join(', ') : String(value);
}

onMounted(loadCustomers);
</script>
