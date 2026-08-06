<?php

namespace App\Http\Requests;

use App\Models\DocumentRequest;
use Illuminate\Foundation\Http\FormRequest;

class SubmitDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document_type_id' => ['required', 'exists:document_types,id'],
            'purpose_id' => ['required', 'exists:request_purposes,id'],
            'purpose_other' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'document_type_id.required' => 'Please select a document type.',
            'purpose_id.required' => 'Please select a purpose.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $resident = $this->user()?->resident;

            if (! $resident) {
                return;
            }

            $activeRequests = DocumentRequest::where('resident_id', $resident->id)
                ->whereIn('status', ['pending', 'reviewing']);

            if ($activeRequests->count() >= 2) {
                $validator->errors()->add(
                    'document_type_id',
                    'You already have 2 active document requests. Please wait for your current requests to be completed or released before requesting again.'
                );

                return;
            }

            if ($activeRequests->clone()
                ->where('document_type_id', $this->input('document_type_id'))
                ->exists()) {
                $validator->errors()->add(
                    'document_type_id',
                    'You already have an active request for this document. Please choose a different document type or wait for the current request to be completed.'
                );
            }
        });
    }
}
