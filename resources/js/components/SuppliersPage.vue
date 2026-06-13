<template>
  <div class="space-y-6 animate-fade-in">
    <!-- En-tête page -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-lg font-semibold text-slate-900">Fournisseurs</h2>
        <p class="text-sm text-slate-500">{{ suppliers.total ?? suppliers.data?.length ?? 0 }} fournisseur(s) partenaire(s)</p>
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
            @keyup.enter="loadSuppliers"
            type="search"
            placeholder="Rechercher..."
            class="input-field pl-9 w-56"
          />
        </div>
        <button @click="loadSuppliers" class="btn-secondary">Chercher</button>
      </div>
    </div>

    <!-- Contenu principal -->
    <div class="grid gap-6 lg:grid-cols-[1fr_380px]">
      <!-- Table -->
      <div class="card overflow-hidden">
        <div class="card-header">
          <h3 class="font-semibold text-slate-900">Liste des fournisseurs</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="data-table">
            <thead>
              <tr>
                <th>Nom</th>
                <th>Contact</th>
                <th>Adresse</th>
                <th class="text-right pr-4">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="supplier in suppliers.data" :key="supplier.id">
                <td class="font-medium text-slate-900">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 font-bold text-xs">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        {{ supplier.name }}
                    </div>
                </td>
                <td>
                    <div class="text-sm">
                        <div v-if="supplier.email" class="flex items-center gap-1.5 text-slate-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            {{ supplier.email }}
                        </div>
                        <div v-if="supplier.phone" class="flex items-center gap-1.5 text-slate-500 mt-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                            {{ supplier.phone }}
                        </div>
                        <span v-if="!supplier.email && !supplier.phone" class="text-slate-400 italic">-</span>
                    </div>
                </td>
                <td>
                    <p class="text-sm text-slate-600 max-w-[200px] truncate">{{ supplier.address || '-' }}</p>
                </td>
                <td class="text-right pr-4">
                  <div class="flex items-center justify-end gap-2">
                    <button @click="editSupplier(supplier)" class="btn-primary btn-sm">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                      </svg>
                      Modifier
                    </button>
                    <button @click="deleteSupplier(supplier.id)" class="btn-danger btn-sm">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                      Supprimer
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!suppliers.data?.length">
                <td colspan="4" class="py-12 text-center">
                  <div class="flex flex-col items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <p class="text-sm text-slate-400">Aucun fournisseur trouvé</p>
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
            {{ editingId ? 'Modifier le fournisseur' : 'Ajouter un fournisseur' }}
          </h3>
          <p class="text-xs text-slate-400 mt-0.5">
            {{ editingId ? 'Mettez à jour les coordonnées' : 'Créer un nouveau profil fournisseur' }}
          </p>
        </div>
        <div class="card-body">
          <form @submit.prevent="saveSupplier" class="space-y-4">
            <div>
              <label class="input-label">Nom d'entreprise</label>
              <input v-model="form.name" type="text" required class="input-field" placeholder="ex: Pharma Plus SA" />
            </div>
            <div>
              <label class="input-label">Email (optionnel)</label>
              <input v-model="form.email" type="email" class="input-field" placeholder="ex: contact@pharmaplus.com" />
            </div>
            <div>
              <label class="input-label">Téléphone (optionnel)</label>
              <input v-model="form.phone" type="text" class="input-field" placeholder="ex: +221 33..." />
            </div>
            <div>
              <label class="input-label">Adresse physique</label>
              <textarea v-model="form.address" rows="3" class="input-field resize-none" placeholder="Adresse complète..."></textarea>
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
    message.value = 'Fournisseur modifié avec succès.';
    setTimeout(() => resetForm(), 2000);
    return;
  }

  const response = await window.axios.post('/api/suppliers', form);
  suppliers.value.data.unshift(response.data);
  message.value = 'Fournisseur ajouté avec succès.';
  setTimeout(() => resetForm(), 2000);
}

async function deleteSupplier(id) {
  if (!confirm('Voulez-vous vraiment supprimer ce fournisseur ?')) return;
  await window.axios.delete(`/api/suppliers/${id}`);
  await loadSuppliers();
}

function cancelEdit() {
  resetForm();
}

onMounted(loadSuppliers);
</script>
