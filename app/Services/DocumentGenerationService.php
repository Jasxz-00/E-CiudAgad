<?php

namespace App\Services;

use App\Models\BarangayOfficial;
use App\Models\DocumentRequest;
use App\Models\DocumentSetting;
use App\Models\IssuedDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DocumentGenerationService
{
    public const TEMPLATE_MAP = [
        'BRGY_CLEARANCE' => 'brgy-clearance',
        'CERT_RESIDENCY' => 'cert-residency',
        'CERT_INDIGENCY' => 'cert-indigency',
        'PACK_BIRTH' => 'pack-birth',
    ];

    public const DEFAULT_TEMPLATE = 'generic';

    public const CLEARANCE_CODES = [
        'BRGY_CLEARANCE',
        'EMPLOYMENT_CLEARANCE',
        'BIZ_PERMIT',
    ];

    public static function isClearanceType(?string $code): bool
    {
        return $code !== null && in_array($code, self::CLEARANCE_CODES, true);
    }

    public function generate(DocumentRequest $documentRequest): IssuedDocument
    {
        $controlNumber = $this->nextControlNumber($documentRequest);

        $data = $this->buildData($documentRequest, $controlNumber);

        $svg = $this->renderSvg($documentRequest, $data);

        $path = 'documents/'.$controlNumber.'.svg';
        Storage::disk('private')->put($path, $svg);

        return IssuedDocument::create([
            'document_request_id' => $documentRequest->id,
            'document_type_id' => $documentRequest->document_type_id,
            'resident_id' => $documentRequest->resident_id,
            'control_number' => $controlNumber,
            'document_data' => $data,
            'svg_path' => $path,
            'pdf_generated' => false,
            'generated_at' => now(),
            'generated_by' => auth()->id(),
        ]);
    }

    public function preview(DocumentRequest $documentRequest): string
    {
        return $this->renderSvg($documentRequest, $this->buildData($documentRequest, null));
    }

    public function renderSvg(DocumentRequest $documentRequest, array $data): string
    {
        $viewName = self::TEMPLATE_MAP[$documentRequest->documentType->code ?? ''] ?? self::DEFAULT_TEMPLATE;

        return view('personnel.documents.templates.'.$viewName, $data)->render();
    }

    public function markPrinted(IssuedDocument $issuedDocument, int $userId): void
    {
        $issuedDocument->update([
            'print_count' => $issuedDocument->print_count + 1,
            'printed_at' => now(),
        ]);
    }

    protected function buildData(DocumentRequest $documentRequest, ?string $controlNumber): array
    {
        $resident = $documentRequest->resident;
        $documentType = $documentRequest->documentType;
        $purpose = $documentRequest->purpose;
        $genericPurposeName = $purpose->name === 'Others' ? $documentRequest->purpose_other : $purpose->name;

        $data = [
            'control_number' => $controlNumber,
            'queue_number' => $documentRequest->queue_number,
            'document_type' => $documentType->name,
            'document_type_code' => $documentType->code,
            'purpose' => $genericPurposeName,
            'purpose_code' => $purpose->code,
            'show_photo' => true,
            'resident' => [
                'full_name' => $resident->last_name.', '.$resident->first_name.($resident->middle_name ? ' '.$resident->middle_name[0].'.' : '').($resident->suffix ? ' '.$resident->suffix : ''),
                'full_name_natural' => $resident->first_name.($resident->middle_name ? ' '.$resident->middle_name : '').' '.$resident->last_name.($resident->suffix ? ' '.$resident->suffix : ''),
                'age' => $resident->age ?? $resident->birthdate?->age,
                'civil_status' => $this->titleCase($resident->civil_status),
                'gender' => $resident->gender,
                'nationality' => $this->titleCase($resident->nationality),
                'place_of_birth' => $this->titleCase($resident->place_of_birth),
                'occupation' => $this->titleCase($resident->occupation),
                'address' => $this->titleCase($resident->full_address),
                'category' => $this->titleCase($resident->category),
                'photo' => $this->imageDataUri($resident->id_picture_path),
            ],
            'issued_on' => now()->format('F j, Y'),
            'prepared_by' => auth()->user()?->username,
            'settings' => $this->settings(),
        ];

        $data['layout_body'] = $documentType->layout_body
            ? $this->replacePlaceholders($documentType->layout_body, $data)
            : null;

        return $data;
    }

    public function replacePlaceholders(string $body, array $data): string
    {
        $values = [
            '{full_name}' => $data['resident']['full_name'],
            '{full_name_natural}' => $data['resident']['full_name_natural'],
            '{age}' => $data['resident']['age'],
            '{civil_status}' => $data['resident']['civil_status'],
            '{gender}' => $data['resident']['gender'],
            '{nationality}' => $data['resident']['nationality'],
            '{place_of_birth}' => $data['resident']['place_of_birth'],
            '{occupation}' => $data['resident']['occupation'],
            '{address}' => $data['resident']['address'],
            '{category}' => $data['resident']['category'],
            '{purpose}' => $data['purpose'],
            '{queue_number}' => $data['queue_number'],
            '{control_number}' => $data['control_number'] ?? '__________',
            '{fee}' => 'FREE',
            '{issued_on}' => $data['issued_on'],
            '{prepared_by}' => $data['prepared_by'] ?? '—',
            '{document_type}' => $data['document_type'],
            '{barangay_name}' => $data['settings']['barangay_name'],
            '{chairman_name}' => $data['settings']['chairman_name'] ?? '________________________',
        ];

        foreach ($values as $token => $value) {
            $body = str_replace($token, e((string) $value), $body);
        }

        return $body;
    }

    public static function placeholderList(): array
    {
        return [
            '{full_name}' => 'Last, First M. (title case format)',
            '{full_name_natural}' => 'First Middle Last (natural order)',
            '{age}' => null,
            '{civil_status}' => null,
            '{gender}' => null,
            '{nationality}' => null,
            '{place_of_birth}' => null,
            '{occupation}' => null,
            '{address}' => null,
            '{category}' => null,
            '{purpose}' => 'Selected request purpose',
            '{queue_number}' => null,
            '{control_number}' => 'Assigned after completion',
            '{fee}' => 'FREE (no processing fee)',
            '{issued_on}' => 'Current date (e.g. August 13, 2026) — wrap in <b>{issued_on}</b> to render bold',
            '{prepared_by}' => 'Processing personnel',
            '{document_type}' => null,
            '{barangay_name}' => null,
            '{chairman_name}' => 'Punong Barangay name',
        ];
    }

    protected function settings(): array
    {
        $setting = DocumentSetting::bootstrap();

        $officials = BarangayOfficial::orderByRaw("CASE position
                WHEN 'punong_barangay' THEN 0
                WHEN 'kagawad' THEN 1
                WHEN 'secretary' THEN 2
                WHEN 'treasurer' THEN 3
                ELSE 4 END")
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn ($official) => [
                'position' => $official->position,
                'position_label' => $official->position_label,
                'name' => $official->name,
            ])
            ->all();

        return [
            'chairman_name' => $setting->chairman_name,
            'barangay_name' => $setting->barangay_name,
            'province_name' => $setting->province_name,
            'city_name' => $setting->city_name,
            'barangay_address' => $setting->barangay_address,
            'left_logo' => $this->logoDataUri($setting->left_logo_path),
            'right_logo' => $this->logoDataUri($setting->right_logo_path),
            'signature' => $this->logoDataUri($setting->signature_path),
            'officials' => $officials,
        ];
    }

    protected function titleCase(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return (string) $value;
        }

        return mb_convert_case(mb_strtolower(trim($value)), MB_CASE_TITLE, 'UTF-8');
    }

    protected function logoDataUri(?string $path): ?string
    {
        return $this->imageDataUri($path);
    }

    protected function imageDataUri(?string $path): ?string
    {
        if (! $path || ! Storage::disk('private')->exists($path)) {
            return null;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($extension) {
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'application/octet-stream',
        };

        return 'data:'.$mime.';base64,'.base64_encode(Storage::disk('private')->get($path));
    }

    protected function nextControlNumber(DocumentRequest $documentRequest): string
    {
        $year = now()->format('Y');

        return DB::transaction(function () use ($documentRequest, $year) {
            $sequence = DB::table('document_sequences')
                ->lockForUpdate()
                ->where('document_type_code', $documentRequest->documentType->code)
                ->where('year', $year)
                ->first();

            $next = $sequence ? $sequence->last_seq + 1 : 1;

            if ($sequence) {
                DB::table('document_sequences')
                    ->where('id', $sequence->id)
                    ->update(['last_seq' => $next, 'updated_at' => now()]);
            } else {
                DB::table('document_sequences')->insert([
                    'document_type_code' => $documentRequest->documentType->code,
                    'year' => $year,
                    'last_seq' => $next,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return 'EC-'.$documentRequest->documentType->code.'-'.$year.'-'.str_pad($next, 4, '0', STR_PAD_LEFT);
        });
    }
}
