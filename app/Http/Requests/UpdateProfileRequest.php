<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contact_number' => ['required', 'string', 'regex:/^09\d{2}-\d{3}-\d{4}$/'],
            'emergency_contact' => ['required', 'string', 'regex:/^09\d{2}-\d{3}-\d{4}$/', 'different:contact_number'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,'.Auth::id()],
            'civil_status' => ['nullable', 'string', 'max:50', 'in:single,married,widowed,divorced'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'barangay' => ['nullable', 'string', 'max:255'],
            'subdivision' => ['nullable', 'string', 'max:255'],
            'purok' => ['nullable', 'string', 'max:50'],
            'religion' => ['nullable', 'string', 'max:100'],
            'place_of_birth' => ['nullable', 'string', 'max:255'],
            'building_no' => ['nullable', 'string', 'max:50'],
            'unit_no' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:255'],
            'province' => ['nullable', 'string', 'max:255'],
            'zip_code' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'contact_number.regex' => 'Contact number must be in format 09XX-XXX-XXXX.',
            'contact_number.required' => 'Please enter your contact number.',
            'emergency_contact.regex' => 'Emergency contact must be in format 09XX-XXX-XXXX.',
            'emergency_contact.required' => 'Please enter an emergency contact number.',
            'emergency_contact.different' => 'Emergency contact must be different from your contact number.',
            'email.email' => 'Please enter a valid email address.',
            'civil_status.in' => 'Selected civil status is not valid.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('occupation')) {
            $this->merge(['occupation' => $this->occupation ? strtoupper(trim($this->occupation)) : null]);
        }
        if ($this->has('street')) {
            $this->merge(['street' => $this->street ? strtoupper(trim($this->street)) : null]);
        }
        if ($this->has('barangay')) {
            $this->merge(['barangay' => $this->barangay ? strtoupper(trim($this->barangay)) : null]);
        }
        if ($this->has('subdivision')) {
            $this->merge(['subdivision' => $this->subdivision ? strtoupper(trim($this->subdivision)) : null]);
        }
        if ($this->has('purok')) {
            $this->merge(['purok' => $this->purok ? strtoupper(trim($this->purok)) : null]);
        }
        if ($this->has('city')) {
            $this->merge(['city' => $this->city ? strtoupper(trim($this->city)) : null]);
        }
        if ($this->has('province')) {
            $this->merge(['province' => $this->province ? strtoupper(trim($this->province)) : null]);
        }
        if ($this->has('place_of_birth')) {
            $this->merge(['place_of_birth' => $this->place_of_birth ? strtoupper(trim($this->place_of_birth)) : null]);
        }
    }
}
