<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactRequestReceived;
use App\Mail\ContactRequestNotification;

class ContactFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $buyer;
    protected $supplier;
    protected $company;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        // Create supplier with verified company
        $this->supplier = User::factory()->create([
            'role' => 'supplier',
        ]);

        $this->company = Company::factory()->create([
            'user_id' => $this->supplier->id,
            'verification_status' => 'verified',
            'verification_id' => 'VEX-12345',
        ]);

        // Create buyer
        $this->buyer = User::factory()->create([
            'role' => 'buyer',
        ]);
    }

    /** @test */
    public function buyer_can_request_contact_from_verified_supplier()
    {
        $response = $this->actingAs($this->buyer)
            ->postJson('/api/contacts/request', [
                'company_id' => $this->company->id,
                'company_name' => 'Test Buyer Company',
                'contact_person' => 'John Doe',
                'email' => 'buyer@example.com',
                'phone' => '+1234567890',
                'country' => 'USA',
                'message' => 'Interested in your products',
                'recaptcha_token' => 'test_token',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('contacts', [
            'company_id' => $this->company->id,
            'email' => 'buyer@example.com',
            'status' => 'pending',
        ]);

        Mail::assertSent(ContactRequestReceived::class);
        Mail::assertSent(ContactRequestNotification::class);
    }

    /** @test */
    public function buyer_cannot_contact_unverified_supplier()
    {
        $this->company->update(['verification_status' => 'pending']);

        $response = $this->actingAs($this->buyer)
            ->postJson('/api/contacts/request', [
                'company_id' => $this->company->id,
                'company_name' => 'Test Buyer Company',
                'contact_person' => 'John Doe',
                'email' => 'buyer@example.com',
                'country' => 'USA',
                'message' => 'Interested in your products',
                'recaptcha_token' => 'test_token',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function supplier_can_respond_to_contact_request()
    {
        $contact = Contact::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->supplier)
            ->postJson("/api/contacts/{$contact->id}/respond", [
                'response_message' => 'Thank you for your interest',
                'share_full_contact' => true,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'status' => 'responded',
        ]);

        $this->assertDatabaseHas('companies', [
            'id' => $this->company->id,
            'show_contact_info' => true,
        ]);
    }

    /** @test */
    public function contact_information_is_masked_by_default()
    {
        $response = $this->getJson("/api/contacts/masked/{$this->company->id}");

        $response->assertStatus(200);
        
        $data = $response->json('data');
        
        // Email should be masked
        $this->assertStringContainsString('***', $data['email']);
        // Phone should be masked
        $this->assertStringContainsString('***', $data['phone']);
    }
}
