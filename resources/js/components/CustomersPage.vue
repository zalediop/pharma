<template>
  <div class="space-y-6">
    <section class="rounded-xl bg-white p-6 shadow-sm">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <h2 class="text-xl font-semibold">Clients</h2>
          <p class="text-slate-600">Gérer les coordonnées et l’historique des clients.</p>
          <p v-if="error" class="mt-2 text-sm text-red-700">{{ error }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <input v-model="search" type="search" placeholder="Rechercher" class="rounded border border-slate-300 px-3 py-2" />
          <button @click="loadCustomers" class="rounded bg-sky-600 px-4 py-2 text-white hover:bg-sky-700">Rechercher</button>
        </div>
      </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-[2fr_1fr]">
      <section class="rounded-xl bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold mb-4">Liste des clients</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50">
              <tr>
                <th class="px-4 py-3">Nom</th>
                <th class="px-4 py-3">Email</th>
                <th class="px-4 py-3">Téléphone</th>
                <th class="px-4 py-3">Médicaments chroniques</th>
                <th class="px-4 py-3">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              <tr v-for="customer in customers.data" :key="customer.id">
                <td class="px-4 py-3">{{ customer.name }}</td>
                <td class="px-4 py-3">{{ customer.email || '-' }}</td>
                <td class="px-4 py-3">{{ customer.phone || '-' }}</td>
                <td class="px-4 py-3">{{ formatChronicMedication(customer.chronic_medication) }}</td>
                <td class="px-4 py-3 space-x-2">
                  <button @click="editCustomer(customer)" class="rounded bg-sky-500 px-3 py-1 text-white hover:bg-sky-600">Modifier</button>
                  <button @click="deleteCustomer(customer.id)" class="rounded bg-red-500 px-3 py-1 text-white hover:bg-red-600">Supprimer</button>
                </td>
              </tr>
              <tr v-if="!customers.data.length">
                <td colspan="5" class="px-4 py-4 text-center text-slate-500">Aucun client trouvé.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section class="rounded-xl bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold mb-4">{{ editingId ? 'Modifier un client' : 'Ajouter un client' }}</h3>
        <form @submit.prevent="saveCustomer" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-700">Nom</label>
            <input v-model="form.name" type="text" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Email</label>
            <input v-model="form.email" type="email" class="mt-1 w-full rounded border border-slate-300 px-3 py-2" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Téléphone</label>
            <input v-model="form.phone" type="text" class="mt-1 w-full rounded border border-slate-300 px-3 py-2" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Médicaments chroniques</label>
            <input v-model="form.chronic_medication" type="text" placeholder="Séparez par des virgules" class="mt-1 w-full rounded border border-slate-300 px-3 py-2" />
          </div>
          <button type="submit" class="rounded bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700">{{ editingId ? 'Enregistrer' : 'Ajouter' }}</button>
          <button v-if="editingId" type="button" @click="cancelEdit" class="rounded bg-slate-300 px-4 py-2 text-slate-700 hover:bg-slate-400">Annuler</button>
          <p v-if="message" class="text-sm text-emerald-700">{{ message }}</p>
        </form>
      </section>
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
    chronic_medication: form.chronic_medication ? form.chronic_medication.split(',').map((item) => item.trim()) : [],
  };

  if (editingId.value) {
    const response = await window.axios.patch(`/api/customers/${editingId.value}`, payload);
    customers.value.data = customers.value.data.map((customer) =>
      customer.id === editingId.value ? response.data : customer
    );
    message.value = 'Client modifié.';
    resetForm();
    return;
  }

  const response = await window.axios.post('/api/customers', payload);
  customers.value.data.unshift(response.data);
  message.value = 'Client ajouté.';
  resetForm();
}

async function deleteCustomer(id) {
  await window.axios.delete(`/api/customers/${id}`);
  await loadCustomers();
}

function cancelEdit() {
  resetForm();
}

function formatChronicMedication(value) {
  if (!value) {
    return '-';
  }
  return Array.isArray(value) ? value.join(', ') : String(value);
}

onMounted(loadCustomers);
</script>
