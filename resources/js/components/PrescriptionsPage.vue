<template>
  <div class="space-y-6">
    <section class="rounded-xl bg-white p-6 shadow-sm">
      <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
          <h2 class="text-xl font-semibold">Ordonnances</h2>
          <p class="text-slate-600">Gérer les ordonnances des patients.</p>
        </div>
        <div class="flex flex-wrap gap-2">
          <input v-model="search" type="search" placeholder="Rechercher" class="rounded border border-slate-300 px-3 py-2" />
          <button @click="loadPrescriptions" class="rounded bg-sky-600 px-4 py-2 text-white hover:bg-sky-700">Rechercher</button>
        </div>
      </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-[2fr_1fr]">
      <section class="rounded-xl bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold mb-4">Liste des ordonnances</h3>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
            <thead class="bg-slate-50">
              <tr>
                <th class="px-4 py-3">Patient</th>
                <th class="px-4 py-3">Médecin</th>
                <th class="px-4 py-3">Date</th>
                <th class="px-4 py-3">Statut</th>
                <th class="px-4 py-3">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              <tr v-for="prescription in prescriptions.data" :key="prescription.id">
                <td class="px-4 py-3">{{ prescription.customer?.name || '-' }}</td>
                <td class="px-4 py-3">{{ prescription.doctor?.name || '-' }}</td>
                <td class="px-4 py-3">{{ new Date(prescription.created_at).toLocaleDateString('fr-FR') }}</td>
                <td class="px-4 py-3">
                  <span :class="['rounded px-2 py-1 text-xs font-semibold', prescription.status === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800']">
                    {{ prescription.status === 'pending' ? 'En attente' : 'Complétée' }}
                  </span>
                </td>
                <td class="px-4 py-3 space-x-2">
                  <button @click="viewPrescription(prescription)" class="rounded bg-sky-500 px-3 py-1 text-white hover:bg-sky-600">Détails</button>
                  <button v-if="prescription.status === 'pending'" @click="completePrescription(prescription.id)" class="rounded bg-emerald-600 px-3 py-1 text-white hover:bg-emerald-700">Valider</button>
                </td>
              </tr>
              <tr v-if="!prescriptions.data.length">
                <td colspan="5" class="px-4 py-4 text-center text-slate-500">Aucune ordonnance trouvée.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section v-if="selectedPrescription" class="rounded-xl bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold mb-4">Détails de l'ordonnance</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-xs font-medium text-slate-600">Patient</label>
            <p class="text-sm font-semibold">{{ selectedPrescription.customer?.name }}</p>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600">Médecin</label>
            <p class="text-sm font-semibold">{{ selectedPrescription.doctor?.name }}</p>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600">Médicaments</label>
            <ul class="mt-2 space-y-1 text-sm">
              <li v-for="item in selectedPrescription.medicines" :key="item.id" class="rounded border border-slate-200 p-2">
                <strong>{{ item.name }}</strong> ({{ item.dosage }}) - Qty: {{ item.pivot.quantity }}
              </li>
            </ul>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600">Notes</label>
            <p class="mt-1 text-sm">{{ selectedPrescription.notes || '-' }}</p>
          </div>
        </div>
      </section>
      <section v-else class="rounded-xl bg-white p-6 shadow-sm">
        <p class="text-slate-500">Sélectionnez une ordonnance pour voir les détails.</p>
      </section>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue';

const prescriptions = ref({ data: [] });
const search = ref('');
const selectedPrescription = ref(null);

async function loadPrescriptions() {
  const response = await window.axios.get('/api/prescriptions', { params: { search: search.value } });
  prescriptions.value = response.data;
  selectedPrescription.value = null;
}

function viewPrescription(prescription) {
  selectedPrescription.value = prescription;
}

async function completePrescription(id) {
  await window.axios.patch(`/api/prescriptions/${id}`, { status: 'completed' });
  await loadPrescriptions();
}

onMounted(loadPrescriptions);
</script>
