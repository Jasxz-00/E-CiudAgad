@extends('layouts.app')

@section('title', __('profile.title'))

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="profileDirtyGuard()">
    <a href="{{ route('resident.dashboard') }}" class="btn-ghost mb-4 inline-flex items-center gap-2 text-sm"
       @click.prevent="navigateAway('{{ route('resident.dashboard') }}')">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        {{ __('common.back_to_dashboard') }}
    </a>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-600/15 text-green-600 dark:text-green-400 border border-success/30 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    <div class="card mb-6">
        <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">{{ __('profile.personal_info') }}</h2>
        <p class="text-xs text-gray-600 dark:text-gray-400 mb-4">{{ __('profile.readonly_field_hint') }}</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-600 dark:text-gray-400">{{ __('profile.tracking_number') }}</span>
                <p class="font-medium font-mono">{{ auth()->user()->tracking_number }}</p>
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">{{ __('profile.full_name') }}</span>
                <p class="font-medium">{{ $resident->full_name }}</p>
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">{{ __('profile.age') }}</span>
                <p class="font-medium">{{ $resident->age }} {{ __('profile.years_old') }}</p>
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">{{ __('profile.gender') }}</span>
                <p class="font-medium capitalize">{{ $resident->gender }}</p>
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">{{ __('profile.birthdate') }}</span>
                <p class="font-medium">{{ $resident->birthdate?->format('F d, Y') ?? '-' }}</p>
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">{{ __('profile.nationality') }}</span>
                <p class="font-medium">{{ $resident->nationality ?? '-' }}</p>
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">{{ __('profile.civil_status') }}</span>
                <p class="font-medium capitalize">{{ $resident->civil_status ?? '-' }}</p>
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">{{ __('profile.category') }}</span>
                <p class="font-medium capitalize">{{ $resident->category ?? '-' }}</p>
            </div>
            <div>
                <span class="text-gray-600 dark:text-gray-400">{{ __('common.status') }}</span>
                <p class="font-medium capitalize">{{ $resident->person_status ?? '-' }}</p>
            </div>
            <div class="sm:col-span-2">
                <span class="text-gray-600 dark:text-gray-400">{{ __('profile.address') }}</span>
                <p class="font-medium">{{ $resident->full_address ?? '-' }}</p>
            </div>
        </div>
    </div>

    <div class="card mb-6">
        <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">{{ __('profile.contact_info') }}</h2>

        <form method="POST" action="{{ route('resident.profile.update') }}" class="space-y-4" x-data="{ submitting: false }" @@submit="submitting = true; dirty = false">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="contact_number" class="label">{{ __('profile.contact_number') }}</label>
                    <input id="contact_number" type="tel" name="contact_number"
                        value="{{ old('contact_number', $resident->contact_number) }}" required
                        class="input-field phone-mask @error('contact_number') input-error @enderror"
                        placeholder="09XX-XXX-XXXX" maxlength="13" autocomplete="tel">
                    @error('contact_number') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="emergency_contact" class="label">{{ __('profile.emergency_contact') }}</label>
                    <input id="emergency_contact" type="tel" name="emergency_contact"
                        value="{{ old('emergency_contact', $resident->emergency_contact) }}" required
                        class="input-field phone-mask @error('emergency_contact') input-error @enderror"
                        placeholder="09XX-XXX-XXXX" maxlength="13" autocomplete="off">
                    @error('emergency_contact') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="label">{{ __('profile.email_address') }}</label>
                    <input id="email" type="email" name="email"
                        value="{{ old('email', auth()->user()->email) }}"
                        class="input-field @error('email') input-error @enderror" autocomplete="email">
                    @error('email') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="civil_status" class="label">{{ __('profile.civil_status') }}</label>
                    <select id="civil_status" name="civil_status" class="select-field" autocomplete="off">
                        <option value="">{{ __('common.select') }}</option>
                        <option value="single" {{ old('civil_status', $resident->civil_status) == 'single' ? 'selected' : '' }}>Single</option>
                        <option value="married" {{ old('civil_status', $resident->civil_status) == 'married' ? 'selected' : '' }}>Married</option>
                        <option value="widowed" {{ old('civil_status', $resident->civil_status) == 'widowed' ? 'selected' : '' }}>Widowed</option>
                        <option value="divorced" {{ old('civil_status', $resident->civil_status) == 'divorced' ? 'selected' : '' }}>Divorced</option>
                    </select>
                </div>

                <div>
                    <label for="religion" class="label">{{ __('profile.religion') }}</label>
                    <input id="religion" type="text" name="religion"
                        value="{{ old('religion', $resident->religion) }}"
                        class="input-field" autocomplete="off">
                </div>

                <div>
                    <label for="occupation" class="label">{{ __('profile.occupation') }}</label>
                    <input id="occupation" type="text" name="occupation"
                        value="{{ old('occupation', $resident->occupation) }}"
                        class="input-field uppercase-input" autocomplete="off"
                        placeholder="e.g., TEACHER">
                </div>

                <div>
                    <label for="place_of_birth" class="label">{{ __('profile.place_of_birth') }}</label>
                    <input id="place_of_birth" type="text" name="place_of_birth"
                        value="{{ old('place_of_birth', $resident->place_of_birth) }}"
                        class="input-field uppercase-input" autocomplete="off">
                </div>

                <div>
                    <label for="building_no" class="label">{{ __('common.building_no') }}</label>
                    <input id="building_no" type="text" name="building_no"
                        value="{{ old('building_no', $resident->building_no) }}"
                        class="input-field" autocomplete="address-line1">
                </div>

                <div>
                    <label for="unit_no" class="label">{{ __('common.unit_no') }}</label>
                    <input id="unit_no" type="text" name="unit_no"
                        value="{{ old('unit_no', $resident->unit_no) }}"
                        class="input-field" autocomplete="address-line2">
                </div>

                <div>
                    <label for="street" class="label">{{ __('profile.street') }}</label>
                    <input id="street" type="text" name="street"
                        value="{{ old('street', $resident->street) }}"
                        class="input-field uppercase-input" autocomplete="street-address"
                        placeholder="e.g., M.H. DEL PILAR ST">
                </div>

                <div>
                    <label for="barangay" class="label">{{ __('profile.barangay') }}</label>
                    <input id="barangay" type="text" name="barangay"
                        value="{{ old('barangay', $resident->barangay) }}"
                        class="input-field uppercase-input" autocomplete="address-level2"
                        placeholder="e.g., MOLINO I">
                </div>

                <div>
                    <label for="subdivision" class="label">{{ __('profile.subdivision') }}</label>
                    <input id="subdivision" type="text" name="subdivision"
                        value="{{ old('subdivision', $resident->subdivision) }}"
                        class="input-field uppercase-input" autocomplete="off"
                        placeholder="e.g., PHASE 1">
                </div>

                <div>
                    <label for="purok" class="label">{{ __('profile.purok') }}</label>
                    <input id="purok" type="text" name="purok"
                        value="{{ old('purok', $resident->purok) }}"
                        class="input-field uppercase-input" autocomplete="off"
                        placeholder="e.g., PUROK 1">
                </div>

                <div>
                    <label for="city" class="label">{{ __('common.city') }}</label>
                    <input id="city" type="text" name="city"
                        value="{{ old('city', $resident->city) }}"
                        class="input-field uppercase-input" autocomplete="address-level1"
                        placeholder="e.g., BACOOR CITY">
                </div>

                <div>
                    <label for="province" class="label">{{ __('common.province') }}</label>
                    <input id="province" type="text" name="province"
                        value="{{ old('province', $resident->province) }}"
                        class="input-field uppercase-input" autocomplete="address-level1"
                        placeholder="e.g., CAVITE">
                </div>

                <div>
                    <label for="zip_code" class="label">{{ __('common.zip_code') }}</label>
                    <input id="zip_code" type="text" name="zip_code"
                        value="{{ old('zip_code', $resident->zip_code) }}"
                        class="input-field" autocomplete="postal-code"
                        placeholder="e.g., 4102">
                </div>
            </div>

            <button type="submit" class="btn-primary" :disabled="submitting">
                <span x-show="!submitting">{{ __('profile.update_profile') }}</span>
                <span x-show="submitting">{{ __('profile.updating') }}</span>
            </button>
        </form>
    </div>

    <div class="card">
        <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 mb-4">{{ __('profile.security') }}</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ __('profile.reset_pin_desc') }}</p>

        <form method="POST" action="{{ route('resident.profile.reset-pin') }}" class="space-y-4 max-w-md" x-data="{ submitting: false }" @@submit="submitting = true">
            @csrf

            <div>
                <label for="current_pin" class="label">{{ __('profile.current_pin') }}</label>
                <input id="current_pin" type="password" name="current_pin" required
                    class="input-field @error('current_pin') input-error @enderror"
                    placeholder="{{ __('profile.current_pin') }}" maxlength="6" inputmode="numeric" autocomplete="off">
                @error('current_pin') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="new_pin" class="label">{{ __('profile.new_pin') }}</label>
                <input id="new_pin" type="password" name="new_pin" required
                    class="input-field @error('new_pin') input-error @enderror"
                    placeholder="{{ __('profile.new_pin') }}" maxlength="6" inputmode="numeric" autocomplete="new-password">
                @error('new_pin') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="new_pin_confirmation" class="label">{{ __('profile.confirm_pin') }}</label>
                <input id="new_pin_confirmation" type="password" name="new_pin_confirmation" required
                    class="input-field"
                    placeholder="{{ __('profile.confirm_pin') }}" maxlength="6" inputmode="numeric" autocomplete="new-password">
            </div>

            <button type="submit" class="btn-accent" :disabled="submitting">
                <span x-show="!submitting">{{ __('profile.reset_pin') }}</span>
                <span x-show="submitting">{{ __('profile.resetting') }}</span>
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function profileDirtyGuard() {
    return {
        dirty: false,
        init() {
            const form = this.$el.querySelector('form');
            if (!form) return;
            const original = {};
            form.querySelectorAll('input, select, textarea').forEach(el => {
                original[el.name] = el.value;
            });
            form.addEventListener('input', () => {
                this.dirty = Array.from(form.querySelectorAll('input, select, textarea')).some(el => el.value !== original[el.name]);
            });
            form.addEventListener('submit', () => { this.dirty = false; });
        },
        navigateAway(url) {
            if (this.dirty && !confirm('{{ __('common.unsaved_changes_confirm') }}')) return;
            window.location.href = url;
        }
    };
}
</script>
@endpush
