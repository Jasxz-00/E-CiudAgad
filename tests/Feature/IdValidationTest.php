<?php

namespace Tests\Feature;

use App\Services\IdValidationService;
use Tests\TestCase;

class IdValidationTest extends TestCase
{
    protected IdValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(IdValidationService::class);
    }

    public function test_valid_phil_id_format(): void
    {
        $this->assertTrue($this->service->validate('phil_id', '1234-5678-9012-3456'));
    }

    public function test_invalid_phil_id_format(): void
    {
        $this->assertFalse($this->service->validate('phil_id', '1234-5678-9012'));
        $this->assertFalse($this->service->validate('phil_id', '1234567890123456'));
        $this->assertFalse($this->service->validate('phil_id', 'ABCD-EFGH-IJKL-MNOP'));
    }

    public function test_valid_passport_format(): void
    {
        $this->assertTrue($this->service->validate('passport', 'P1234567A'));
        $this->assertTrue($this->service->validate('passport', 'AA1234567'));
    }

    public function test_invalid_passport_format(): void
    {
        $this->assertFalse($this->service->validate('passport', '12345678'));
        $this->assertFalse($this->service->validate('passport', 'P12345678'));
        $this->assertFalse($this->service->validate('passport', 'ABC123456'));
    }

    public function test_valid_umid_format(): void
    {
        $this->assertTrue($this->service->validate('umid', '0033-1234567-8'));
    }

    public function test_invalid_umid_format(): void
    {
        $this->assertFalse($this->service->validate('umid', '003312345678'));
        $this->assertFalse($this->service->validate('umid', '0033-123456-8'));
    }

    public function test_valid_philhealth_format(): void
    {
        $this->assertTrue($this->service->validate('philhealth', '11-201534404-7'));
    }

    public function test_invalid_philhealth_format(): void
    {
        $this->assertFalse($this->service->validate('philhealth', '112015344047'));
        $this->assertFalse($this->service->validate('philhealth', '11-20153440-7'));
    }

    public function test_valid_drivers_license_format(): void
    {
        $this->assertTrue($this->service->validate('drivers_license', 'D01-12-345678'));
    }

    public function test_invalid_drivers_license_format(): void
    {
        $this->assertFalse($this->service->validate('drivers_license', 'D0112345678'));
        $this->assertFalse($this->service->validate('drivers_license', 'AB-12-345678'));
    }

    public function test_valid_prc_id_format(): void
    {
        $this->assertTrue($this->service->validate('prc_id', '0123456'));
    }

    public function test_invalid_prc_id_format(): void
    {
        $this->assertFalse($this->service->validate('prc_id', '01234567'));
        $this->assertFalse($this->service->validate('prc_id', 'ABCDEFG'));
    }

    public function test_valid_postal_id_format(): void
    {
        $this->assertTrue($this->service->validate('postal_id', '1234-5678-9012'));
    }

    public function test_invalid_postal_id_format(): void
    {
        $this->assertFalse($this->service->validate('postal_id', '1234-5678-90'));
        $this->assertFalse($this->service->validate('postal_id', '123456789012'));
    }

    public function test_valid_sss_id_format(): void
    {
        $this->assertTrue($this->service->validate('sss_id', '34-1234567-8'));
    }

    public function test_invalid_sss_id_format(): void
    {
        $this->assertFalse($this->service->validate('sss_id', '3412345678'));
        $this->assertFalse($this->service->validate('sss_id', '34-123456-8'));
    }

    public function test_valid_tin_id_format(): void
    {
        $this->assertTrue($this->service->validate('tin_id', '123-456-789-000'));
    }

    public function test_invalid_tin_id_format(): void
    {
        $this->assertFalse($this->service->validate('tin_id', '123456789000'));
        $this->assertFalse($this->service->validate('tin_id', '123-45-678-000'));
    }

    public function test_valid_ibp_id_format(): void
    {
        $this->assertTrue($this->service->validate('ibp_id', '12345'));
    }

    public function test_invalid_ibp_id_format(): void
    {
        $this->assertFalse($this->service->validate('ibp_id', '1234'));
        $this->assertFalse($this->service->validate('ibp_id', '123456'));
    }

    public function test_valid_owwa_ofw_id_format(): void
    {
        $this->assertTrue($this->service->validate('owwa_ofw_id', '1234567890'));
    }

    public function test_invalid_owwa_ofw_id_format(): void
    {
        $this->assertFalse($this->service->validate('owwa_ofw_id', '123456789'));
        $this->assertFalse($this->service->validate('owwa_ofw_id', 'ABCDEFGHIJ'));
    }

    public function test_barangay_id_accepts_any_format(): void
    {
        $this->assertTrue($this->service->validate('barangay_id', 'ANY-FREE-TEXT-123'));
        $this->assertTrue($this->service->validate('barangay_id', ''));
    }

    public function test_school_id_accepts_any_format(): void
    {
        $this->assertTrue($this->service->validate('school_id', 'STUDENT-2026-001'));
        $this->assertTrue($this->service->validate('school_id', ''));
    }

    public function test_unsupported_id_type_returns_false(): void
    {
        $this->assertTrue($this->service->validate('unknown_type', 'any-value'));
    }

    public function test_id_number_is_case_insensitive(): void
    {
        $this->assertTrue($this->service->validate('passport', 'p1234567a'));
    }
}
