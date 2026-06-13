<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Doctor;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        Sanctum::actingAs($this->user);
    }

    public function test_dynamic_limit_pagination_on_endpoints(): void
    {
        Customer::factory()->count(25)->create();

        // Default pagination limit is 20
        $response = $this->getJson('/api/customers');
        $response->assertStatus(200);
        $this->assertCount(20, $response->json('data'));

        // Custom pagination limit of 5
        $response = $this->getJson('/api/customers?limit=5');
        $response->assertStatus(200);
        $this->assertCount(5, $response->json('data'));

        // Custom pagination limit of 1000 cap
        $response = $this->getJson('/api/customers?limit=1000');
        $response->assertStatus(200);
        $this->assertCount(25, $response->json('data'));
    }

    public function test_prescription_search_by_various_fields(): void
    {
        $doc1 = Doctor::create([
            'name' => 'Jean Martin',
            'specialty' => 'Cardiologue',
            'license_number' => '12345',
        ]);
        $doc2 = Doctor::create([
            'name' => 'Aicha Diop',
            'specialty' => 'Pediatre',
            'license_number' => '67890',
        ]);

        $cust1 = Customer::factory()->create(['name' => 'Moussa Ndiaye']);
        $cust2 = Customer::factory()->create(['name' => 'Fatu Sall']);

        $med = Medicine::create([
            'name' => 'Versol',
            'dci' => 'Versol',
            'form' => 'Comprimé',
            'dosage' => '500mg',
            'category' => 'Antibiotique',
        ]);

        $pres1 = Prescription::create([
            'customer_id' => $cust1->id,
            'doctor_id' => $doc1->id,
            'issued_at' => now(),
            'notes' => 'Prendre un comprimé le matin',
        ]);
        $pres1->medicines()->attach($med->id, ['quantity' => 10]);

        $pres2 = Prescription::create([
            'customer_id' => $cust2->id,
            'doctor_id' => $doc2->id,
            'issued_at' => now(),
            'notes' => 'Prendre deux comprimés le soir',
        ]);
        $pres2->medicines()->attach($med->id, ['quantity' => 20]);

        // Search by patient name
        $response = $this->getJson('/api/prescriptions?search=Moussa');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($cust1->id, $response->json('data.0.customer_id'));

        // Search by doctor name
        $response = $this->getJson('/api/prescriptions?search=Aicha');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($cust2->id, $response->json('data.0.customer_id'));

        // Search by notes
        $response = $this->getJson('/api/prescriptions?search=soir');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($cust2->id, $response->json('data.0.customer_id'));
    }

    public function test_archived_medicines_excluded_from_stock_and_alerts(): void
    {
        $med1 = Medicine::create([
            'name' => 'Paracétamol',
            'dci' => 'Paracétamol',
            'form' => 'Comprimé',
            'dosage' => '1000mg',
            'category' => 'Antalgique',
        ]);

        $med2 = Medicine::create([
            'name' => 'Archived Medicine',
            'dci' => 'Archived',
            'form' => 'Comprimé',
            'dosage' => '500mg',
            'category' => 'Antalgique',
            'archived' => true,
        ]);

        // Create stock under threshold for both
        Stock::create([
            'medicine_id' => $med1->id,
            'quantity' => 5,
            'expiry_date' => now()->addDays(15),
            'alert_threshold' => 10,
        ]);

        Stock::create([
            'medicine_id' => $med2->id,
            'quantity' => 3,
            'expiry_date' => now()->addDays(10),
            'alert_threshold' => 10,
        ]);

        // Verify stock list only contains non-archived
        $response = $this->getJson('/api/stocks');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($med1->id, $response->json('data.0.medicine_id'));

        // Verify alerts list only contains non-archived alerts
        $response = $this->getJson('/api/alerts');
        $response->assertStatus(200);

        // We expect 2 alerts for med1 (low stock + expiring soon) and 0 for med2
        $alerts = $response->json();
        $this->assertNotEmpty($alerts);
        foreach ($alerts as $alert) {
            $this->assertStringContainsString('Paracétamol', $alert['message']);
            $this->assertStringNotContainsString('Archived Medicine', $alert['message']);
        }
    }
}
