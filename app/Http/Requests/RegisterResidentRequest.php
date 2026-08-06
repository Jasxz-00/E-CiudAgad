<?php

namespace App\Http\Requests;

use App\Rules\ValidateGovernmentID;
use App\Services\IdValidationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterResidentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:255', 'regex:/^[A-Z\s]+$/'],
            'last_name' => ['required', 'string', 'max:255', 'regex:/^[A-Z\s]+$/'],
            'middle_name' => ['nullable', 'string', 'max:255', 'regex:/^[A-Z\s]*$/'],
            'suffix' => ['nullable', 'string', 'max:10', 'in:JR.,SR.,II,III,IV,V'],
            'birthdate_month' => ['required', 'integer', 'between:1,12'],
            'birthdate_day' => ['required', 'integer', 'between:1,31'],
            'birthdate_year' => ['required', 'integer', 'between:1900,'.date('Y')],
            'gender' => ['required', 'in:male,female'],
            'civil_status' => ['nullable', 'string', 'max:50', Rule::in(['single', 'married', 'widowed', 'divorced'])],
            'nationality' => ['nullable', 'string', 'max:100', 'regex:/^[A-Z\s]+$/'],
            'occupation' => ['nullable', 'string', 'max:255', 'regex:/^[A-Z\s]*$/'],
            'religion' => ['nullable', 'string', 'max:100'],
            'place_of_birth' => ['nullable', 'string', 'max:255'],
            'building_no' => ['nullable', 'string', 'max:50'],
            'unit_no' => ['nullable', 'string', 'max:50'],
            'street' => ['nullable', 'string', 'max:255'],
            'road' => ['nullable', 'string', 'max:255'],
            'barangay' => ['nullable', 'string', 'max:255'],
            'subdivision' => ['nullable', 'string', 'max:255'],
            'purok' => ['nullable', 'string', 'max:50'],
            'contact_number' => ['required', 'string', 'regex:/^09\d{2}-\d{3}-\d{4}$/'],
            'emergency_contact' => [
                'required',
                'string',
                'regex:/^09\d{2}-\d{3}-\d{4}$/',
                function ($attribute, $value, $fail) {
                    if ($value === $this->contact_number) {
                        $fail(__('registration.emergency_not_equal'));
                    }
                },
            ],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'middle_name_none' => ['nullable', 'boolean'],
            'person_status' => ['nullable', 'string', 'in:pwd,senior,pregnant'],
            'status_verification_photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'id_type' => ['required', 'string', 'max:50'],
            'id_number' => [
                'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) {
                    $idType = $this->id_type;
                    $service = app(IdValidationService::class);
                    if (! $service->validate($idType, $value)) {
                        $patternDesc = $service->getPatternDescription($idType);
                        $msg = __('registration.id_format_error');
                        if ($patternDesc) {
                            $msg .= ' '.__('Expected format').': '.$patternDesc;
                        }
                        $fail($msg);
                    }
                },
            ],
            'id_scan' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png',
                'max:5120',
                new ValidateGovernmentID(
                    idType: $this->input('id_type', ''),
                    firstName: $this->input('first_name', ''),
                    lastName: $this->input('last_name', ''),
                    addressText: $this->getAddressText(),
                ),
            ],
            'document_type_id' => ['required', 'exists:document_types,id'],
            'purpose_id' => ['required', 'exists:request_purposes,id'],
            'purpose_other' => ['nullable', 'string', 'max:255'],
            'privacy_consent' => ['required', 'accepted'],
            'is_pregnant' => ['nullable', 'boolean'],
            'assisted_mode' => ['nullable', 'in:0,1'],
            'submission_id' => ['nullable', 'string', 'max:36'],
            'staff_badge' => ['nullable', 'string', 'max:50'],
            'ocr_confidence' => ['nullable', 'integer', 'min:0', 'max:100'],
            'ocr_extracted' => ['nullable', 'string', 'max:100'],
        ];

        if ($this->filled('person_status')) {
            $rules['status_verification_photo'][] = 'required';
        }

        if ($this->input('assisted_mode') === '1') {
            $rules['staff_badge'][] = 'required';
            $rules['privacy_consent'] = ['nullable'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'first_name.regex' => __('First name must contain only uppercase letters and spaces.'),
            'first_name.required' => __('Please enter your first name.'),
            'last_name.regex' => __('Last name must contain only uppercase letters and spaces.'),
            'last_name.required' => __('Please enter your last name.'),
            'middle_name.regex' => __('Middle name must contain only uppercase letters and spaces.'),
            'contact_number.regex' => __('Contact number must be in format 09XX-XXX-XXXX.'),
            'contact_number.required' => __('Please enter your contact number.'),
            'emergency_contact.regex' => __('Emergency contact must be in format 09XX-XXX-XXXX.'),
            'emergency_contact.required' => __('Please enter an emergency contact number.'),
            'privacy_consent.accepted' => __('You must accept the data privacy consent to proceed.'),
            'id_scan.required' => __('Please upload a clear image of your government ID.'),
            'id_scan.mimes' => __('ID scan must be a JPG, JPEG, or PNG image.'),
            'id_scan.max' => __('ID scan must not exceed 5MB.'),
            'gender.required' => __('Please select your sex.'),
            'document_type_id.required' => __('Please select a document type.'),
            'purpose_id.required' => __('Please select a purpose.'),
            'birthdate_month.required' => __('Please select your birth month.'),
            'birthdate_day.required' => __('Please select your birth day.'),
            'birthdate_year.required' => __('Please select your birth year.'),
            'email.email' => __('Please enter a valid email address.'),
            'email.unique' => __('This email address is already registered.'),
            'civil_status.in' => __('Please select a valid civil status.'),
            'staff_badge.required' => __('Staff badge number is required for assisted registration.'),
            'status_verification_photo.required' => __('A verification photo is required for your selected priority category.'),
        ];
    }

    protected function getAddressText(): ?string
    {
        $parts = [];
        if ($this->filled('street')) {
            $parts[] = $this->input('street');
        }
        if ($this->filled('barangay')) {
            $parts[] = $this->input('barangay');
        }
        if ($this->filled('city')) {
            $parts[] = $this->input('city');
        }
        if ($this->filled('province')) {
            $parts[] = $this->input('province');
        }

        return ! empty($parts) ? implode(', ', $parts) : null;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('first_name')) {
            $this->merge(['first_name' => strtoupper(trim($this->first_name))]);
        }
        if ($this->has('last_name')) {
            $this->merge(['last_name' => strtoupper(trim($this->last_name))]);
        }
        if ($this->has('middle_name')) {
            $this->merge(['middle_name' => $this->middle_name ? strtoupper(trim($this->middle_name)) : null]);
        }
        if ($this->has('suffix')) {
            $this->merge(['suffix' => $this->suffix ? strtoupper(trim($this->suffix)) : null]);
        }
        if ($this->has('nationality')) {
            $this->merge(['nationality' => $this->nationality ? strtoupper(trim($this->nationality)) : null]);
        }
        if ($this->has('occupation')) {
            $this->merge(['occupation' => $this->occupation ? strtoupper(trim($this->occupation)) : null]);
        }
        if ($this->has('building_no')) {
            $this->merge(['building_no' => $this->building_no ? strtoupper(trim($this->building_no)) : null]);
        }
        if ($this->has('unit_no')) {
            $this->merge(['unit_no' => $this->unit_no ? strtoupper(trim($this->unit_no)) : null]);
        }
        if ($this->has('id_number')) {
            $this->merge(['id_number' => strtoupper(trim($this->id_number))]);
        }
        if ($this->has('tracking_number')) {
            $this->merge(['tracking_number' => strtoupper(trim($this->tracking_number))]);
        }
        if ($this->has('street')) {
            $this->merge(['street' => $this->street ? strtoupper(trim($this->street)) : null]);
        }
        if ($this->has('road')) {
            $this->merge(['road' => $this->road ? strtoupper(trim($this->road)) : null]);
        }
        if ($this->has('barangay')) {
            $this->merge(['barangay' => $this->barangay ? strtoupper(trim($this->barangay)) : null]);
        }
        if ($this->has('purok')) {
            $this->merge(['purok' => $this->purok ? strtoupper(trim($this->purok)) : null]);
        }
        if ($this->has('subdivision')) {
            $this->merge(['subdivision' => $this->subdivision ? strtoupper(trim($this->subdivision)) : null]);
        }
        if ($this->has('place_of_birth')) {
            $this->merge(['place_of_birth' => $this->place_of_birth ? strtoupper(trim($this->place_of_birth)) : null]);
        }
    }
}
