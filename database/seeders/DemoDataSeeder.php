<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\Doctor;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Créer des fournisseurs
        $suppliers = Supplier::factory(5)->create();

        // Créer des médecins
        $doctors = Doctor::factory(8)->create();

        // Créer des médicaments
        $medicines = [
            [
                'name' => 'Amoxicilline',
                'dci' => 'Amoxicilline',
                'form' => 'Comprimé',
                'dosage' => '500mg',
                'category' => 'Antibiotique',
            ],
            [
                'name' => 'Paracétamol',
                'dci' => 'Paracétamol',
                'form' => 'Comprimé',
                'dosage' => '1000mg',
                'category' => 'Antalgique',
            ],
            [
                'name' => 'Ibuprofen',
                'dci' => 'Ibuprofen',
                'form' => 'Comprimé',
                'dosage' => '400mg',
                'category' => 'Anti-inflammatoire',
            ],
            [
                'name' => 'Metformine',
                'dci' => 'Metformine',
                'form' => 'Comprimé',
                'dosage' => '850mg',
                'category' => 'Antidiabétique',
            ],
            [
                'name' => 'Losartan',
                'dci' => 'Losartan',
                'form' => 'Comprimé',
                'dosage' => '50mg',
                'category' => 'Antihypertenseur',
            ],
            [
                'name' => 'Atorvastatine',
                'dci' => 'Atorvastatine',
                'form' => 'Comprimé',
                'dosage' => '20mg',
                'category' => 'Statine',
            ],
            [
                'name' => 'Oméprazole',
                'dci' => 'Oméprazole',
                'form' => 'Gélule',
                'dosage' => '20mg',
                'category' => 'Inhibiteur de pompe à protons',
            ],
            [
                'name' => 'Vitamine C',
                'dci' => 'Acide ascorbique',
                'form' => 'Comprimé',
                'dosage' => '1000mg',
                'category' => 'Vitamine',
            ],
        ];

        $createdMedicines = [];
        foreach ($medicines as $medicineData) {
            $createdMedicines[] = Medicine::create($medicineData);
        }

        // Créer du stock pour les médicaments
        foreach ($createdMedicines as $medicine) {
            Stock::factory(3)->create([
                'medicine_id' => $medicine->id,
            ]);
        }

        // Créer des clients
        $customers = Customer::factory(10)->create();

        // Créer des ordonnances
        foreach ($customers as $customer) {
            if (rand(0, 1)) {
                $prescription = Prescription::create([
                    'customer_id' => $customer->id,
                    'issued_at' => now()->subDays(rand(1, 30)),
                    'notes' => rand(0, 1) ? 'À prendre pendant les repas.' : null,
                    'status' => rand(0, 1) ? 'pending' : 'completed',
                ]);

                // Attacher des médicaments à la ordonnance
                $selectedMedicines = collect($createdMedicines)->random(rand(1, 3));
                foreach ($selectedMedicines as $medicine) {
                    $prescription->medicines()->attach($medicine->id, [
                        'quantity' => rand(1, 3),
                    ]);
                }
            }
        }
    }
}
