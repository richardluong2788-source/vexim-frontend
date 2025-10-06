<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Package;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $supplier;
    protected $company;
    protected $package;

    protected function setUp(): void
    {
        parent::setUp();

        // Create supplier with company
        $this->supplier = User::factory()->create([
            'role' => 'supplier',
        ]);

        $this->company = Company::factory()->create([
            'user_id' => $this->supplier->id,
        ]);

        // Create package
        $this->package = Package::factory()->create([
            'name' => 'Gold',
            'price' => 299.00,
            'duration_days' => 365,
        ]);
    }

    /** @test */
    public function supplier_can_initiate_payment()
    {
        $response = $this->actingAs($this->supplier)
            ->postJson('/api/payments/initiate', [
                'package_id' => $this->package->id,
                'payment_method' => 'stripe',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('payments', [
            'company_id' => $this->company->id,
            'package_id' => $this->package->id,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function payment_completion_upgrades_company_package()
    {
        $payment = Payment::factory()->create([
            'company_id' => $this->company->id,
            'package_id' => $this->package->id,
            'status' => 'pending',
        ]);

        $response = $this->postJson('/api/payments/callback', [
            'transaction_id' => $payment->transaction_id,
            'status' => 'completed',
            'payment_reference' => 'stripe_ch_123456',
        ], [
            'X-Payment-Method' => 'stripe',
            'Stripe-Signature' => 'test_signature',
        ]);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('companies', [
            'id' => $this->company->id,
            'package_id' => $this->package->id,
        ]);

        $this->assertNotNull($this->company->fresh()->package_expires_at);
    }

    /** @test */
    public function supplier_can_view_payment_history()
    {
        Payment::factory()->count(3)->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->actingAs($this->supplier)
            ->getJson('/api/payments/history');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => ['id', 'amount', 'status', 'created_at']
                    ]
                ]
            ]);
    }

    /** @test */
    public function webhook_without_valid_signature_is_rejected()
    {
        $payment = Payment::factory()->create([
            'status' => 'pending',
        ]);

        $response = $this->postJson('/api/payments/callback', [
            'transaction_id' => $payment->transaction_id,
            'status' => 'completed',
        ]);

        $response->assertStatus(401);
    }
}
