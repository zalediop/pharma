<template>
  <div class="space-y-6 animate-fade-in">
    <!-- En-tête page -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-lg font-semibold text-slate-900">Médicaments</h2>
        <p class="text-sm text-slate-500">{{ medicines.total ?? medicines.data?.length ?? 0 }} médicament(s) dans le catalogue</p>
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
            @keyup.enter="loadMedicines"
            type="search"
            placeholder="Rechercher..."
            class="input-field pl-9 w-56"
          />
        </div>
        <button @click="loadMedicines" class="btn-secondary">Chercher</button>
      </div>
    </div>

    <!-- Contenu principal -->
    <div class="grid gap-6 lg:grid-cols-[1fr_380px]">
      <!-- Table -->
      <div class="card overflow-hidden">
        <div class="card-header">
          <h3 class="font-semibold text-slate-900">Liste des médicaments</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="data-table">
            <thead>
              <tr>
                <th>Nom</th>
                <th>DCI</th>
                <th>Forme</th>
                <th>Dosage</th>
                <th>Catégorie</th>
                <th class="text-right pr-4">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="medicine in medicines.data" :key="medicine.id">
                <td class="font-medium text-slate-900">{{ medicine.name }}</td>
                <td class="text-slate-500 italic">{{ medicine.dci }}</td>
                <td>
                  <span class="badge-indigo">{{ medicine.form }}</span>
                </td>
                <td class="font-mono text-xs text-slate-600">{{ medicine.dosage }}</td>
                <td>
                  <span class="badge-blue">{{ medicine.category }}</span>
                </td>
                <td class="text-right pr-4">
                  <div class="flex items-center justify-end gap-2">
                    <button @click="editMedicine(medicine)" class="btn-primary btn-sm">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                      </svg>
                      Modifier
                    </button>
                    <button @click="archiveMedicine(medicine.id)" class="btn-warning btn-sm">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                      </svg>
                      Archiver
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!medicines.data?.length">
                <td colspan="6" class="py-12 text-center">
                  <div class="flex flex-col items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                    <p class="text-sm text-slate-400">Aucun médicament trouvé</p>
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
            {{ editingId ? 'Modifier le médicament' : 'Ajouter un médicament' }}
          </h3>
          <p class="text-xs text-slate-400 mt-0.5">
            {{ editingId ? 'Modifiez les informations ci-dessous' : 'Remplissez le formulaire ci-dessous' }}
          </p>
        </div>
        <div class="card-body">
          <form @submit.prevent="saveMedicine" class="space-y-4">
            <div>
              <label class="input-label">Nom commercial</label>
              <input v-model="form.name" type="text" required class="input-field" placeholder="ex: Paracétamol" />
            </div>
            <div>
              <label class="input-label">DCI</label>
              <input v-model="form.dci" type="text" required class="input-field" placeholder="ex: Paracétamol" />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="input-label">Forme</label>
                <input v-model="form.form" type="text" required class="input-field" placeholder="ex: Comprimé" />
              </div>
              <div>
                <label class="input-label">Dosage</label>
                <input v-model="form.dosage" type="text" required class="input-field" placeholder="ex: 500mg" />
              </div>
            </div>
            <div>
              <label class="input-label">Catégorie</label>
              <input v-model="form.category" type="text" required class="input-field" placeholder="ex: Antalgique" />
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

const medicines = ref({ data: [] });
const search = ref('');
const editingId = ref(null);
const form = reactive({ name: '', dci: '', form: '', dosage: '', category: '' });
const message = ref('');

async function loadMedicines() {
  const response = await window.axios.get('/api/medicines', { params: { search: search.value } });
  medicines.value = response.data;
}

function resetForm() {
  editingId.value = null;
  Object.assign(form, { name: '', dci: '', form: '', dosage: '', category: '' });
  message.value = '';
}

function editMedicine(medicine) {
  editingId.value = medicine.id;
  Object.assign(form, { name: medicine.name, dci: medicine.dci, form: medicine.form, dosage: medicine.dosage, category: medicine.category });
  message.value = '';
}

async function saveMedicine() {
  if (editingId.value) {
    const response = await window.axios.patch(`/api/medicines/${editingId.value}`, form);
    medicines.value.data = medicines.value.data.map(m => m.id === editingId.value ? response.data : m);
    message.value = 'Médicament modifié avec succès.';
    setTimeout(() => resetForm(), 2000);
    return;
  }
  const response = await window.axios.post('/api/medicines', form);
  medicines.value.data.unshift(response.data);
  message.value = 'Médicament ajouté avec succès.';
  setTimeout(() => resetForm(), 2000);
}

async function archiveMedicine(id) {
  await window.axios.delete(`/api/medicines/${id}`);
  await loadMedicines();
}

function cancelEdit() { resetForm(); }

onMounted(loadMedicines);
</script>
