@extends('layouts.app')

@section('title', 'Register Resident')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">Register Resident (Personnel-Assisted)</h1>

    <div class="card mb-6">
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Use this form to register a resident on their behalf. All fields follow the same rules as the resident-facing registration.</p>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-600/15 text-green-600 dark:text-green-400 border border-success/30 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    <div class="card">
        <form method="POST" action="{{ route('personnel.registrations.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            <input type="hidden" name="assisted_mode" value="1">

            <div class="p-4 bg-accent-50 dark:bg-accent-900/20 border border-accent-200 dark:border-accent-800 rounded-xl">
                <label for="staff_badge" class="label">Staff Badge / ID Number <span class="text-red-600 dark:text-red-400">*</span></label>
                <input id="staff_badge" type="text" name="staff_badge" value="{{ old('staff_badge') }}" required
                       class="input-field" placeholder="Enter your staff ID badge number" autocomplete="off">
                <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">Enter your personnel ID badge for audit purposes.</p>
                @error('staff_badge') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 pb-2">Personal Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input name="first_name" label="First Name" required :value="old('first_name')" class="uppercase-input" placeholder="JUAN" autocomplete="given-name" />
                <x-input name="last_name" label="Last Name" required :value="old('last_name')" class="uppercase-input" placeholder="DELA CRUZ" autocomplete="family-name" />
                <x-input name="middle_name" label="Middle Name" :value="old('middle_name')" class="uppercase-input" placeholder="SANTOS" autocomplete="additional-name" />
                <div>
                    <label for="suffix" class="label">Suffix</label>
                    <select id="suffix" name="suffix" class="select-field" autocomplete="honorific-suffix">
                        <option value="">N/A</option>
                        <option value="JR." {{ old('suffix') == 'JR.' ? 'selected' : '' }}>JR.</option>
                        <option value="SR." {{ old('suffix') == 'SR.' ? 'selected' : '' }}>SR.</option>
                        <option value="II" {{ old('suffix') == 'II' ? 'selected' : '' }}>II</option>
                        <option value="III" {{ old('suffix') == 'III' ? 'selected' : '' }}>III</option>
                        <option value="IV" {{ old('suffix') == 'IV' ? 'selected' : '' }}>IV</option>
                        <option value="V" {{ old('suffix') == 'V' ? 'selected' : '' }}>V</option>
                    </select>
                </div>
                <x-input name="nationality" label="Nationality" required :value="old('nationality', 'FILIPINO')" class="uppercase-input" autocomplete="off" />
                <x-input name="occupation" label="Occupation" :value="old('occupation')" class="uppercase-input" placeholder="e.g., TEACHER" autocomplete="off" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="birthdate_month" class="label">Birthdate <span class="text-red-600 dark:text-red-400">*</span></label>
                    <div class="grid grid-cols-3 gap-1.5 sm:gap-2">
                        <select id="birthdate_month" name="birthdate_month" class="select-field" required autocomplete="bday-month">
                            <option value="">Month</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ old('birthdate_month') == $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                            @endforeach
                        </select>
                        <select id="birthdate_day" name="birthdate_day" class="select-field" required autocomplete="bday-day">
                            <option value="">Day</option>
                            @foreach(range(1, 31) as $d)
                                <option value="{{ $d }}" {{ old('birthdate_day') == $d ? 'selected' : '' }}>{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}</option>
                            @endforeach
                        </select>
                        <select id="birthdate_year" name="birthdate_year" class="select-field" required autocomplete="bday-year">
                            <option value="">Year</option>
                            @foreach(range(date('Y'), 1950) as $y)
                                <option value="{{ $y }}" {{ old('birthdate_year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('birthdate_month') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    @error('birthdate_day') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    @error('birthdate_year') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="gender" class="label">Gender <span class="text-red-600 dark:text-red-400">*</span></label>
                    <select id="gender" name="gender" class="select-field" required autocomplete="sex">
                        <option value="">Select</option>
                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                    @error('gender') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="civil_status" class="label">Civil Status <span class="text-red-600 dark:text-red-400">*</span></label>
                    <select id="civil_status" name="civil_status" class="select-field" autocomplete="off">
                        <option value="">Select</option>
                        @foreach(['single','married','widowed','divorced'] as $cs)
                            <option value="{{ $cs }}" {{ old('civil_status') == $cs ? 'selected' : '' }}>{{ ucfirst($cs) }}</option>
                        @endforeach
                    </select>
                </div>
                <x-input name="religion" label="Religion" :value="old('religion')" placeholder="e.g., ROMAN CATHOLIC" autocomplete="off" />
                <x-input name="place_of_birth" label="Place of Birth" :value="old('place_of_birth')" class="uppercase-input" placeholder="e.g., BACOOR CITY, CAVITE" autocomplete="off" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="person_status" class="label">Person Status</label>
                    <select id="person_status" name="person_status" class="select-field" autocomplete="off">
                        <option value="">Regular</option>
                        <option value="pwd" {{ old('person_status') == 'pwd' ? 'selected' : '' }}>PWD</option>
                        <option value="senior" {{ old('person_status') == 'senior' ? 'selected' : '' }}>Senior Citizen</option>
                        <option value="pregnant" {{ old('person_status') == 'pregnant' ? 'selected' : '' }}>Pregnant</option>
                    </select>
                </div>
                <div>
                    <label for="status_verification_photo" class="label">Verification Photo (if PWD/Senior/Pregnant)</label>
                    <input id="status_verification_photo" type="file" name="status_verification_photo" accept=".jpg,.jpeg,.png,.pdf" autocomplete="off" class="block w-full text-sm text-gray-600 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 dark:bg-primary-900/30 file:text-primary-700">
                </div>
            </div>
            <div class="flex items-center gap-2 mt-4">
                <input type="checkbox" name="is_pregnant" id="is_pregnant" value="1" {{ old('is_pregnant') ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-200 dark:border-gray-700 text-primary-700">
                <label for="is_pregnant" class="text-sm text-gray-600 dark:text-gray-400">Currently pregnant</label>
            </div>

            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 pb-2 mt-6">Address</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input name="building_no" label="Building No." :value="old('building_no')" class="uppercase-input" placeholder="10" autocomplete="address-line1" />
                <x-input name="unit_no" label="Unit No." :value="old('unit_no')" class="uppercase-input" placeholder="49" autocomplete="address-line2" />
                <x-input name="street" label="Street" :value="old('street')" class="uppercase-input" placeholder="e.g., M.H. DEL PILAR ST" autocomplete="street-address" />
                <x-input name="road" label="Road" :value="old('road', 'MOLINO ROAD')" class="uppercase-input" placeholder="MOLINO ROAD" autocomplete="off" />
                <x-input name="barangay" label="Barangay" :value="old('barangay', 'MOLINO I')" class="uppercase-input" placeholder="MOLINO I" autocomplete="address-level2" />
                <x-input name="subdivision" label="Subdivision" :value="old('subdivision')" class="uppercase-input" placeholder="e.g., PHASE 1" autocomplete="off" />
                <x-input name="purok" label="Purok/Zone" :value="old('purok')" class="uppercase-input" placeholder="e.g., PUROK 1" autocomplete="off" />
            </div>

            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 pb-2 mt-6">Contact Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-input name="contact_number" label="Contact Number" required :value="old('contact_number')" placeholder="09XX-XXX-XXXX" maxlength="13" autocomplete="tel" />
                <x-input name="emergency_contact" label="Emergency Contact" required :value="old('emergency_contact')" placeholder="09XX-XXX-XXXX" maxlength="13" autocomplete="off" />
                <x-input name="email" label="Email Address" type="email" :value="old('email')" placeholder="juan@example.com" autocomplete="email" />
            </div>

            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 pb-2 mt-6">Identity Verification</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="id_type" class="label">ID Type <span class="text-red-600 dark:text-red-400">*</span></label>
                    <select id="id_type" name="id_type" class="select-field" required autocomplete="off">
                        <option value="">Select ID Type</option>
                        <option value="phil_id" {{ old('id_type') == 'phil_id' ? 'selected' : '' }}>Philippine National ID</option>
                        <option value="passport" {{ old('id_type') == 'passport' ? 'selected' : '' }}>Passport</option>
                        <option value="umid" {{ old('id_type') == 'umid' ? 'selected' : '' }}>UMID</option>
                        <option value="philhealth" {{ old('id_type') == 'philhealth' ? 'selected' : '' }}>PhilHealth</option>
                        <option value="drivers_license" {{ old('id_type') == 'drivers_license' ? 'selected' : '' }}>Driver's License</option>
                        <option value="prc_id" {{ old('id_type') == 'prc_id' ? 'selected' : '' }}>PRC ID</option>
                        <option value="postal_id" {{ old('id_type') == 'postal_id' ? 'selected' : '' }}>Postal ID</option>
                        <option value="sss_id" {{ old('id_type') == 'sss_id' ? 'selected' : '' }}>SSS ID</option>
                        <option value="tin_id" {{ old('id_type') == 'tin_id' ? 'selected' : '' }}>TIN ID</option>
                        <option value="ibp_id" {{ old('id_type') == 'ibp_id' ? 'selected' : '' }}>IBP ID</option>
                        <option value="owwa_ofw_id" {{ old('id_type') == 'owwa_ofw_id' ? 'selected' : '' }}>OWWA/OFW ID</option>
                        <option value="barangay_id" {{ old('id_type') == 'barangay_id' ? 'selected' : '' }}>Barangay ID</option>
                        <option value="school_id" {{ old('id_type') == 'school_id' ? 'selected' : '' }}>School ID</option>
                    </select>
                    @error('id_type') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
            <div class="p-4 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl">
                <label for="id_scan_front" class="label font-semibold text-gray-900 dark:text-gray-100">Front of ID <span class="text-red-600 dark:text-red-400">*</span></label>
                <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">Upload a clear photo of the front side of the ID showing the name and photo.</p>
                <input id="id_scan_front" type="file" name="id_scan_front" accept=".jpg,.jpeg,.png"
                       class="block w-full text-sm text-gray-600 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 dark:bg-primary-900/30 file:text-primary-700 hover:file:bg-primary-100 dark:bg-primary-900/30"
                       autocomplete="off">
                <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">Accepted: JPG, JPEG, PNG</p>
                @error('id_scan_front') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="p-4 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl">
                <label for="id_scan_back" class="label font-semibold text-gray-900 dark:text-gray-100">Back of ID <span class="text-red-600 dark:text-red-400">*</span></label>
                <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">Upload a clear photo of the back side of the ID.</p>
                <input id="id_scan_back" type="file" name="id_scan_back" accept=".jpg,.jpeg,.png"
                       class="block w-full text-sm text-gray-600 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-primary-50 dark:bg-primary-900/30 file:text-primary-700 hover:file:bg-primary-100 dark:bg-primary-900/30"
                       autocomplete="off">
                <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">Accepted: JPG, JPEG, PNG</p>
                @error('id_scan_back') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 pb-2 mt-6">Document Request</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="document_type_id" class="label">Document Type <span class="text-red-600 dark:text-red-400">*</span></label>
                    <select id="document_type_id" name="document_type_id" class="select-field" required autocomplete="off">
                        <option value="">Select Document</option>
                        @foreach($documentTypes as $doc)
                            <option value="{{ $doc->id }}" {{ old('document_type_id') == $doc->id ? 'selected' : '' }}>{{ $doc->name }}</option>
                        @endforeach
                    </select>
                    @error('document_type_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="purpose_id" class="label">Purpose <span class="text-red-600 dark:text-red-400">*</span></label>
                    <select id="purpose_id" name="purpose_id" class="select-field" required autocomplete="off">
                        <option value="">Select Purpose</option>
                        @foreach($purposes as $purpose)
                            <option value="{{ $purpose->id }}" {{ old('purpose_id') == $purpose->id ? 'selected' : '' }}>{{ $purpose->name }}</option>
                        @endforeach
                        @if($othersPurpose)
                            <option value="{{ $othersPurpose->id }}" {{ old('purpose_id') == $othersPurpose->id ? 'selected' : '' }}>Others</option>
                        @endif
                    </select>
                    @error('purpose_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="pt-4 flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">Register Resident</button>
                <a href="{{ route('personnel.dashboard') }}" class="btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
