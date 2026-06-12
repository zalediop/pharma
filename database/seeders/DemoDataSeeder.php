<?php

namespace Database\Seeders;

use App\Models\Medicine;
use App\Models\Stock;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Créer des médicaments
        $medicines = [
            [
                'name' => 'Versol',
                'dci' => 'Versol',
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
                'name' => 'Genset',
                'dci' => 'Genset',
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
                'dci' => 'Grippex',
                'form' => 'Poudre',
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

        // Créer du stock initial pour les médicaments
        foreach ($createdMedicines as $medicine) {
            Stock::factory(3)->create([
                'medicine_id' => $medicine->id,
            ]);
        }
    }
}
