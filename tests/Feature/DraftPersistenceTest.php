<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DraftPersistenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\DocumentTypeSeeder']);
        Artisan::call('db:seed', ['--class' => 'Database\Seeders\RequestPurposeSeeder']);
    }

    public function test_registration_page_contains_draft_restore_script(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('registration_draft', false);
        $response->assertSee('restoreDraft', false);
        $response->assertSee('saveDraft', false);
    }

    public function test_registration_page_contains_local_storage_draft_key(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('localStorage.getItem(\'registration_draft\')', false);
        $response->assertSee('localStorage.setItem(\'registration_draft\'', false);
        $response->assertSee('localStorage.removeItem(\'registration_draft\'', false);
    }

    public function test_draft_is_saved_on_input_change(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('@input.debounce.500ms="saveDraft()"', false);
    }

    public function test_draft_is_restored_on_page_load(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('restoreDraft()', false);
        $response->assertSee('init()', false);
    }

    public function test_clear_form_button_does_not_exist(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertDontSee('clearForm', false);
        $response->assertDontSee('Burahin ang Form', false);
    }

    public function test_registration_lang_toggle_persists(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('localStorage.getItem(\'registration_lang\')', false);
        $response->assertSee('localStorage.setItem(\'registration_lang\'', false);
    }

    public function test_nationality_defaults_to_filipino(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('FILIPINO');
    }

    public function test_civil_status_includes_divorced(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('divorced', false);
        $response->assertSee('value="divorced"', false);
        $response->assertDontSee('value="separated"', false);
    }

    public function test_barangay_defaults_to_molino_i(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('MOLINO I');
    }

    public function test_person_status_field_exists(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('person_status', false);
    }

    public function test_id_front_back_upload_state_is_present(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertSee('idScanFrontSelected', false);
        $response->assertSee('idScanBackSelected', false);
        $response->assertSee('id_scan_front', false);
        $response->assertSee('id_scan_back', false);
    }
}
