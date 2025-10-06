<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\SupplierDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class VerificationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $supplier;
    protected $company;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@vexim.com',
        ]);

        // Create supplier user with company
        $this->supplier = User::factory()->create([
            'role' => 'supplier',
        ]);

        $this->company = Company::factory()->create([
            'user_id' => $this->supplier->id,
            'verification_status' => 'pending',
        ]);
    }

    /** @test */
    public function supplier_can_upload_verification_documents()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('business_license.pdf', 1000);

        $response = $this->actingAs($this->supplier)
            ->postJson('/api/verification/upload-document', [
                'document_type' => 'business_license',
                'document' => $file,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('supplier_documents', [
            'company_id' => $this->company->id,
            'document_type' => 'business_license',
        ]);

        Storage::disk('public')->assertExists('documents/' . $file->hashName());
    }

    /** @test */
    public function admin_can_approve_verification()
    {
        $response = $this->actingAs($this->admin)
            ->postJson("/api/admin/companies/{$this->company->id}/verify", [
                'action' => 'approve',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('companies', [
            'id' => $this->company->id,
            'verification_status' => 'verified',
        ]);

        $this->assertNotNull($this->company->fresh()->verification_id);
        $this->assertNotNull($this->company->fresh()->verified_at);
    }

    /** @test */
    public function admin_can_reject_verification()
    {
        $response = $this->actingAs($this->admin)
            ->postJson("/api/admin/companies/{$this->company->id}/verify", [
                'action' => 'reject',
                'rejection_reason' => 'Incomplete documents',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('companies', [
            'id' => $this->company->id,
            'verification_status' => 'rejected',
            'rejection_reason' => 'Incomplete documents',
        ]);
    }

    /** @test */
    public function non_admin_cannot_approve_verification()
    {
        $response = $this->actingAs($this->supplier)
            ->postJson("/api/admin/companies/{$this->company->id}/verify", [
                'action' => 'approve',
            ]);

        $response->assertStatus(403);
    }
}
