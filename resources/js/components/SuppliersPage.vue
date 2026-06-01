<template>
  <div class="space-y-6">
    <section class="rounded-xl bg-white p-6 shadow-sm">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <h2 class="text-xl font-semibold">Fournisseurs</h2>
          <p class="text-slate-600">Gérer les fournisseurs de la pharmacie.</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <input v-model="search" type="search" placeholder="Rechercher" class="rounded border border-slate-300 px-3 py-2" />
          <button @click="loadSuppliers" class="rounded bg-sky-600 px-4 py-2 text-white hover:bg-sky-700">Rechercher</button>
        </div>
      </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-[2fr_1fr]">
      <section class="rounded-xl bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold mb-4">Liste des fournisseurs</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50">
              <tr>
                <th class="px-4 py-3">Nom</th>
                <th class="px-4 py-3">Email</th>
                <th class="px-4 py-3">Téléphone</th>
                <th class="px-4 py-3">Adresse</th>
                <th class="px-4 py-3">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              <tr v-for="supplier in suppliers.data" :key="supplier.id">
                <td class="px-4 py-3">{{ supplier.name }}</td>
                <td class="px-4 py-3">{{ supplier.email || '-' }}</td>
                <td class="px-4 py-3">{{ supplier.phone || '-' }}</td>
                <td class="px-4 py-3">{{ supplier.address || '-' }}</td>
                <td class="px-4 py-3 space-x-2">
                  <button @click="editSupplier(supplier)" class="rounded bg-sky-500 px-3 py-1 text-white hover:bg-sky-600">Modifier</button>
                  <button @click="deleteSupplier(supplier.id)" class="rounded bg-red-500 px-3 py-1 text-white hover:bg-red-600">Supprimer</button>
                </td>
              </tr>
              <tr v-if="!suppliers.data.length">
                <td colspan="5" class="px-4 py-4 text-center text-slate-500">Aucun fournisseur trouvé.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section class="rounded-xl bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold mb-4">{{ editingId ? 'Modifier un fournisseur' : 'Ajouter un fournisseur' }}</h3>
        <form @submit.prevent="saveSupplier" class="space-y-4">
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
            <label class="block text-sm font-medium text-slate-700">Adresse</label>
            <textarea v-model="form.address" rows="3" class="mt-1 w-full rounded border border-slate-300 px-3 py-2"></textarea>
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

const suppliers = ref({ data: [] });
const search = ref('');
const editingId = ref(null);
const form = reactive({ name: '', email: '', phone: '', address: '' });
const message = ref('');

async function loadSuppliers() {
  const response = await window.axios.get('/api/suppliers', { params: { search: search.value } });
  suppliers.value = response.data;
}

function resetForm() {
  editingId.value = null;
  Object.assign(form, { name: '', email: '', phone: '', address: '' });
  message.value = '';
}

function editSupplier(supplier) {
  editingId.value = supplier.id;
  Object.assign(form, {
    name: supplier.name,
    email: supplier.email || '',
    phone: supplier.phone || '',
    address: supplier.address || '',
  });
  message.value = '';
}

async function saveSupplier() {
  if (editingId.value) {
    const response = await window.axios.patch(`/api/suppliers/${editingId.value}`, form);
    suppliers.value.data = suppliers.value.data.map((supplier) =>
      supplier.id === editingId.value ? response.data : supplier
    );
    message.value = 'Fournisseur modifié.';
    resetForm();
    return;
  }

  const response = await window.axios.post('/api/suppliers', form);
  suppliers.value.data.unshift(response.data);
  message.value = 'Fournisseur ajouté.';
  resetForm();
}

async function deleteSupplier(id) {
  await window.axios.delete(`/api/suppliers/${id}`);
  await loadSuppliers();
}

function cancelEdit() {
  resetForm();
}

onMounted(loadSuppliers);
</script>
