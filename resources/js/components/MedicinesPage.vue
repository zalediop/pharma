<template>
  <div class="space-y-6">
    <div class="rounded-xl bg-white p-6 shadow-sm">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <h2 class="text-xl font-semibold">Médicaments</h2>
          <p class="text-slate-600">Ajouter, modifier ou archiver des médicaments.</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <input v-model="search" type="search" placeholder="Rechercher" class="rounded border border-slate-300 px-3 py-2" />
          <button @click="loadMedicines" class="rounded bg-sky-600 px-4 py-2 text-white hover:bg-sky-700">Rechercher</button>
        </div>
      </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[2fr_1fr]">
      <section class="rounded-xl bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold mb-4">Liste des médicaments</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50">
              <tr>
                <th class="px-4 py-3">Nom</th>
                <th class="px-4 py-3">DCI</th>
                <th class="px-4 py-3">Forme</th>
                <th class="px-4 py-3">Dosage</th>
                <th class="px-4 py-3">Catégorie</th>
                <th class="px-4 py-3">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              <tr v-for="medicine in medicines.data" :key="medicine.id">
                <td class="px-4 py-3">{{ medicine.name }}</td>
                <td class="px-4 py-3">{{ medicine.dci }}</td>
                <td class="px-4 py-3">{{ medicine.form }}</td>
                <td class="px-4 py-3">{{ medicine.dosage }}</td>
                <td class="px-4 py-3">{{ medicine.category }}</td>
                <td class="px-4 py-3 space-x-2">
                  <button @click="editMedicine(medicine)" class="rounded bg-sky-500 px-3 py-1 text-white hover:bg-sky-600">Modifier</button>
                  <button @click="archiveMedicine(medicine.id)" class="rounded bg-amber-500 px-3 py-1 text-white hover:bg-amber-600">Archiver</button>
                </td>
              </tr>
              <tr v-if="!medicines.data.length">
                <td colspan="6" class="px-4 py-4 text-center text-slate-500">Aucun médicament trouvé.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section class="rounded-xl bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold mb-4">{{ editingId ? 'Modifier un médicament' : 'Ajouter un médicament' }}</h3>
        <form @submit.prevent="saveMedicine" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-700">Nom</label>
            <input v-model="form.name" type="text" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">DCI</label>
            <input v-model="form.dci" type="text" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Forme</label>
            <input v-model="form.form" type="text" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Dosage</label>
            <input v-model="form.dosage" type="text" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Catégorie</label>
            <input v-model="form.category" type="text" required class="mt-1 w-full rounded border border-slate-300 px-3 py-2" />
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
  Object.assign(form, {
    name: medicine.name,
    dci: medicine.dci,
    form: medicine.form,
    dosage: medicine.dosage,
    category: medicine.category,
  });
  message.value = '';
}

async function saveMedicine() {
  if (editingId.value) {
    const response = await window.axios.patch(`/api/medicines/${editingId.value}`, form);
    medicines.value.data = medicines.value.data.map((medicine) =>
      medicine.id === editingId.value ? response.data : medicine
    );
    message.value = 'Médicament modifié.';
    resetForm();
    return;
  }

  const response = await window.axios.post('/api/medicines', form);
  medicines.value.data.unshift(response.data);
  message.value = 'Médicament ajouté.';
  resetForm();
}

async function archiveMedicine(id) {
  await window.axios.delete(`/api/medicines/${id}`);
  await loadMedicines();
}

function cancelEdit() {
  resetForm();
}

onMounted(loadMedicines);
</script>
