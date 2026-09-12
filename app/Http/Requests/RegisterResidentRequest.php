<?php

namespace App\Http\Requests;

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
            'middle_name_none' => ['nullable', 'boolean'],
            'suffix' => ['nullable', 'string', 'max:10', 'in:JR.,SR.,II,III,IV,V'],
            'birthdate_month' => ['required', 'integer', 'between:1,12'],
            'birthdate_day' => ['required', 'integer', 'between:1,31'],
            'birthdate_year' => ['required', 'integer', 'between:1900,'.date('Y')],
            'gender' => ['required', 'in:male,female'],
            'civil_status' => ['required', 'string', 'max:50', Rule::in(['single', 'married', 'widowed', 'divorced'])],
            'nationality' => ['required', 'string', 'max:100', 'regex:/^[A-Z\s]+$/'],
            'occupation' => ['nullable', 'string', 'max:255', 'regex:/^[A-Z\s]*$/'],
            'religion' => ['nullable', 'string', 'max:100'],
            'place_of_birth' => ['required', 'string', 'max:255'],
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
            'person_status' => ['nullable', 'string', 'in:pwd,senior,pregnant'],
            'status_verification_photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'id_type' => ['required', 'string', 'max:50'],
            'id_scan_front' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png',
                'max:10240',
            ],
            'id_scan_back' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png',
                'max:10240',
            ],
            'proof_document' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png',
                'max:10240',
            ],
            'id_1x1' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png',
                'max:10240',
            ],
            'document_type_id' => ['required', 'exists:document_types,id'],
            'proof_type' => ['required', 'in:valid_id,birth_certificate,baptismal_certificate,hoa_certificate'],
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

        $docType = $this->input('document_type_id');
        $docTypeModel = \App\Models\DocumentType::find($docType);

        if ($docTypeModel) {
            $rules['purpose_id'] = ['required', 'exists:request_purposes,id'];
            $rules['purpose_other'] = ['nullable'];
        }

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
            'id_scan_front.required' => __('Please upload a clear image of the FRONT side of your government ID.'),
            'id_scan_front.mimes' => __('ID front must be a JPG, JPEG, or PNG image.'),
            'id_scan_front.max' => __('ID front must not exceed 10MB.'),
            'id_scan_back.required' => __('Please upload a clear image of the BACK side of your government ID.'),
            'id_scan_back.mimes' => __('ID back must be a JPG, JPEG, or PNG image.'),
            'id_scan_back.max' => __('ID back must not exceed 10MB.'),
            'id_1x1.max' => __('1x1 photo must not exceed 10MB.'),
            'proof_type.required' => __('Please select a proof type.'),
            'proof_type.in' => __('Please select a valid proof type.'),
            'gender.required' => __('Please select your sex.'),
            'document_type_id.required' => __('Please select a document type.'),
            'purpose_id.required' => __('Please select a purpose.'),
            'birthdate_month.required' => __('Please select your birth month.'),
            'birthdate_day.required' => __('Please select your birth day.'),
            'birthdate_year.required' => __('Please select your birth year.'),
            'birthdate_month.between' => __('Please provide a valid birthdate.'),
            'birthdate_day.between' => __('Please provide a valid birthdate.'),
            'birthdate_year.between' => __('Please provide a valid birthdate.'),
            
            'nationality.required' => __('Please enter your nationality.'),
            'civil_status.required' => __('Please select your civil status.'),
            'place_of_birth.required' => __('Please enter your place of birth.'),
            'email.email' => __('Please enter a valid email address.'),
            'email.unique' => __('This email address is already registered.'),
            'civil_status.in' => __('Please select a valid civil status.'),
            'staff_badge.required' => __('Staff badge number is required for assisted registration.'),
            'status_verification_photo.required' => __('A verification photo is required for your selected priority category.'),
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            $month = (int) $this->input('birthdate_month');
            $day = (int) $this->input('birthdate_day');
            $year = (int) $this->input('birthdate_year');

            if ($month >= 1 && $month <= 12 && $day >= 1 && $day <= 31 && $year >= 1900 && $year <= (int) date('Y')) {
                if (! checkdate($month, $day, $year)) {
                    $validator->errors()->add('birthdate_day', __('Please provide a valid birthdate.'));
                }
            }

            $documentTypeId = $this->input('document_type_id');
            $purposeId = $this->input('purpose_id');

            if ($documentTypeId && $purposeId) {
                $mapped = \Illuminate\Support\Facades\DB::table('document_type_purposes')
                    ->where('document_type_id', $documentTypeId)
                    ->where('request_purpose_id', $purposeId)
                    ->exists();

                if (! $mapped) {
                    $validator->errors()->add('purpose_id', __('The selected purpose is not available for the chosen document.'));
                }
            }
        });
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('first_name')) {
            $this->merge(['first_name' => strtoupper(trim($this->input('first_name')))]);
        }
        if ($this->has('last_name')) {
            $this->merge(['last_name' => strtoupper(trim($this->input('last_name')))]);
        }
        if ($this->has('middle_name')) {
            $this->merge(['middle_name' => $this->input('middle_name') ? strtoupper(trim($this->input('middle_name'))) : null]);
        }
        if ($this->has('suffix')) {
            $this->merge(['suffix' => $this->input('suffix') ? strtoupper(trim($this->input('suffix'))) : null]);
        }
        if ($this->has('nationality')) {
            $this->merge(['nationality' => $this->input('nationality') ? strtoupper(trim($this->input('nationality'))) : null]);
        }
        if ($this->has('occupation')) {
            $this->merge(['occupation' => $this->input('occupation') ? strtoupper(trim($this->input('occupation'))) : null]);
        }
        if ($this->has('building_no')) {
            $this->merge(['building_no' => $this->input('building_no') ? strtoupper(trim($this->input('building_no'))) : null]);
        }
        if ($this->has('unit_no')) {
            $this->merge(['unit_no' => $this->input('unit_no') ? strtoupper(trim($this->input('unit_no'))) : null]);
        }
        if ($this->has('tracking_number')) {
            $this->merge(['tracking_number' => strtoupper(trim($this->input('tracking_number')))]);
        }
        if ($this->has('street')) {
            $this->merge(['street' => $this->input('street') ? strtoupper(trim($this->input('street'))) : null]);
        }
        if ($this->has('road')) {
            $this->merge(['road' => $this->input('road') ? strtoupper(trim($this->input('road'))) : null]);
        }
        if ($this->has('barangay')) {
            $this->merge(['barangay' => $this->input('barangay') ? strtoupper(trim($this->input('barangay'))) : null]);
        }
        if ($this->has('purok')) {
            $this->merge(['purok' => $this->input('purok') ? strtoupper(trim($this->input('purok'))) : null]);
        }
        if ($this->has('subdivision')) {
            $this->merge(['subdivision' => $this->input('subdivision') ? strtoupper(trim($this->input('subdivision'))) : null]);
        }
        if ($this->has('place_of_birth')) {
            $this->merge(['place_of_birth' => $this->input('place_of_birth') ? strtoupper(trim($this->input('place_of_birth'))) : null]);
        }
        if ($this->has('person_status')) {
            $this->merge(['person_status' => $this->input('person_status') ?: null]);
        }
        if ($this->has('middle_name_none')) {
            $this->merge(['middle_name_none' => (bool) $this->input('middle_name_none')]);
        }
        if ($this->has('is_pregnant')) {
            $this->merge(['is_pregnant' => (bool) $this->input('is_pregnant')]);
        }
    }
}