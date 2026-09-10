<?php

namespace Tests\Feature;

use App\Models\DocumentRequest;
use App\Models\DocumentSetting;
use App\Models\DocumentType;
use App\Models\RequestPurpose;
use App\Models\Resident;
use App\Models\User;
use App\Services\DocumentGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentTemplateFormattingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\RolePermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\DocumentTypeSeeder']);
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\RequestPurposeSeeder']);
    }

    private function makeResident(): Resident
    {
        $user = User::factory()->create(['role' => 'resident']);
        $user->assignRole('resident');

        return Resident::factory()->create(['user_id' => $user->id]);
    }

    private function makeRequest(string $documentCode): DocumentRequest
    {
        $documentType = DocumentType::where('code', $documentCode)->firstOrFail();
        $purpose = RequestPurpose::where('code', 'MEDICAL_ASSISTANCE')->firstOrFail();

        return DocumentRequest::create([
            'reference_number' => 'REQ-TEST-'.$documentCode,
            'queue_number' => 'Q-TEST-'.$documentCode,
            'resident_id' => $this->makeResident()->id,
            'document_type_id' => $documentType->id,
            'purpose_id' => $purpose->id,
            'status' => 'pending',
        ]);
    }

    public function test_all_template_bodies_render_issued_date_in_bold(): void
    {
        $day = now()->format('jS');

        foreach (['BRGY_CLEARANCE', 'CERT_RESIDENCY', 'CERT_INDIGENCY', 'CERT_BARANGAY_CERT'] as $code) {
            $svg = app(DocumentGenerationService::class)->preview($this->makeRequest($code));

            $this->assertStringContainsString('<b>'.$day.' day of', $svg, "Issued date must be bold in {$code}.");
            $this->assertStringContainsString('</b>', $svg);
            $this->assertStringContainsString('(Sgd.)', $svg);
        }
    }

    public function test_letterhead_renders_signature_image_when_uploaded(): void
    {
        Storage::fake('private');
        Storage::disk('private')->put('document_settings/signature_test.png', 'fake-signature-bytes');

        $setting = DocumentSetting::bootstrap();
        $setting->update(['signature_path' => 'document_settings/signature_test.png']);

        $svg = app(DocumentGenerationService::class)->preview($this->makeRequest('CERT_RESIDENCY'));

        $this->assertStringContainsString('data:image/png;base64,', $svg);
        $this->assertStringContainsString('preserveAspectRatio="xMidYMid meet"', $svg);
        $this->assertSame(1, substr_count($svg, '(Sgd.)'), 'Only the secretary signature fallback should remain.');
    }
}
