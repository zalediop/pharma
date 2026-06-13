<template>
  <div class="space-y-6 animate-fade-in">
    <!-- En-tête page -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-lg font-semibold text-slate-900">Ordonnances</h2>
        <p class="text-sm text-slate-500">{{ prescriptions.total ?? prescriptions.data?.length ?? 0 }} ordonnance(s) enregistrée(s)</p>
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
            @keyup.enter="loadPrescriptions"
            type="search"
            placeholder="Rechercher..."
            class="input-field pl-9 w-56"
          />
        </div>
        <button @click="loadPrescriptions" class="btn-secondary">Chercher</button>
      </div>
    </div>

    <!-- Contenu principal -->
    <div class="grid gap-6 xl:grid-cols-[2fr_1.2fr]">
      <!-- Table -->
      <div class="card overflow-hidden">
        <div class="card-header">
          <h3 class="font-semibold text-slate-900">Registre des ordonnances</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="data-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>Patient</th>
                <th>Médecin prescripteur</th>
                <th>Statut</th>
                <th class="text-right pr-4">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="prescription in prescriptions.data" :key="prescription.id" :class="{'bg-slate-50/50': prescription.status === 'completed'}">
                <td class="font-medium text-slate-900">
                    {{ new Date(prescription.created_at).toLocaleDateString('fr-FR') }}
                </td>
                <td class="text-slate-800 font-medium">
                    {{ prescription.customer?.name || 'Inconnu' }}
                </td>
                <td>
                    <div class="flex items-center gap-2 text-slate-600 text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Dr. {{ prescription.doctor?.name || 'Inconnu' }}
                    </div>
                </td>
                <td>
                  <span v-if="prescription.status === 'pending'" class="badge-amber">En attente</span>
                  <span v-else class="badge-green">Complétée</span>
                </td>
                <td class="text-right pr-4">
                  <div class="flex items-center justify-end gap-2">
                      <button @click="viewPrescription(prescription)" class="btn-secondary btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Ouvrir
                      </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!prescriptions.data?.length">
                <td colspan="5" class="py-12 text-center">
                  <div class="flex flex-col items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-sm text-slate-400">Aucune ordonnance trouvée</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Panneau Détails Ordonnance -->
      <div v-if="selectedPrescription" class="card h-fit border-t-4 border-t-indigo-500 sticky top-24">
        <div class="card-header flex items-center justify-between bg-slate-50/50">
          <h3 class="font-semibold text-slate-900 flex items-center gap-2">
              Détails de l'ordonnance
          </h3>
          <button @click="selectedPrescription = null" class="text-slate-400 hover:text-slate-600 rounded-full p-1 transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
          </button>
        </div>
        <div class="card-body space-y-6">
          <div class="flex items-center justify-between pb-4 border-b border-slate-100">
              <div>
                  <p class="text-xs font-semibold uppercase text-slate-400 tracking-wider">Patient</p>
                  <p class="font-medium text-slate-900 mt-1 flex items-center gap-2">
                      {{ selectedPrescription.customer?.name }}
                  </p>
              </div>
              <div class="text-right">
                  <p class="text-xs font-semibold uppercase text-slate-400 tracking-wider">Date</p>
                  <p class="font-medium text-slate-900 mt-1">{{ new Date(selectedPrescription.created_at).toLocaleDateString('fr-FR') }}</p>
              </div>
          </div>

          <div>
            <p class="text-xs font-semibold uppercase text-slate-400 tracking-wider mb-3">Médecin traitant</p>
            <div class="flex items-center gap-3 bg-slate-50 p-3 rounded-xl ring-1 ring-slate-200">
                <div class="h-10 w-10 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center font-bold">
                    Dr
                </div>
                <div>
                    <p class="font-semibold text-slate-900">{{ selectedPrescription.doctor?.name }}</p>
                    <p class="text-xs text-slate-500">{{ selectedPrescription.doctor?.speciality || 'Médecine générale' }}</p>
                </div>
            </div>
          </div>

          <div>
            <p class="text-xs font-semibold uppercase text-slate-400 tracking-wider mb-3">Prescription ({{ selectedPrescription.medicines?.length || 0 }})</p>
            <ul class="space-y-2">
                <li v-for="item in selectedPrescription.medicines" :key="item.id" class="rounded-xl border border-slate-200 p-3 bg-white shadow-sm flex items-start gap-3">
                    <div class="mt-0.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-900">{{ item.name }}</p>
                        <p class="text-xs text-slate-500">{{ item.form }} - {{ item.dosage }}</p>
                        <div class="mt-2 inline-flex items-center gap-1 rounded bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700">
                            Quantité: <span class="font-bold">{{ item.pivot.quantity }}</span>
                        </div>
                    </div>
                </li>
            </ul>
          </div>

          <div v-if="selectedPrescription.notes">
            <p class="text-xs font-semibold uppercase text-slate-400 tracking-wider mb-2">Notes & Posologie</p>
            <div class="bg-amber-50 rounded-xl p-3 ring-1 ring-amber-100 text-sm text-amber-900">
                {{ selectedPrescription.notes }}
            </div>
          </div>

          <div class="pt-4 border-t border-slate-100">
              <button v-if="selectedPrescription.status === 'pending'" @click="completePrescription(selectedPrescription.id)" class="btn-success w-full justify-center py-2.5">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  Marquer comme délivrée
              </button>
              <div v-else class="flex items-center justify-center gap-2 bg-emerald-50 text-emerald-700 py-2.5 rounded-xl text-sm font-semibold ring-1 ring-emerald-100">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Médicaments délivrés
              </div>
          </div>
        </div>
      </div>

      <!-- Placeholder quand rien n'est sélectionné -->
      <div v-else class="card flex flex-col items-center justify-center py-16 text-center h-fit border-dashed border-2 border-slate-200 bg-slate-50/50 shadow-none">
          <div class="h-16 w-16 bg-white rounded-full flex items-center justify-center shadow-sm mb-4 ring-1 ring-slate-100">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
          </div>
          <h3 class="text-slate-700 font-medium">Aucune ordonnance sélectionnée</h3>
          <p class="text-slate-400 text-sm mt-1 max-w-[250px]">Cliquez sur "Ouvrir" dans la liste pour voir les détails d'une prescription.</p>
      </div>

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
