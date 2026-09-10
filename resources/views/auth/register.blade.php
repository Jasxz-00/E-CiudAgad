@extends('layouts.guest')

@section('title', 'Register')

@section('content')
<div class="min-h-[calc(100vh-4rem)] py-12 px-4" x-data="registrationForm({{ json_encode($translations) }})" x-init="init()">
    <div class="max-w-3xl mx-auto">
        <x-card>
            <div class="text-center mb-8">
                <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-4 mb-4">
                    <button type="button" @@click="toggleLang()"
                            class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold rounded-full border transition-colors duration-200"
                            :class="lang === 'en' ? 'bg-primary-700 text-white border-primary-600' : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border-gray-200 dark:border-gray-700'">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span x-text="lang === 'en' ? 'Filipino' : 'English'"></span>
                    </button>
                </div>

                <div class="w-16 h-16 bg-accent-50 dark:bg-accent-900/20 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-accent-700 dark:text-accent-300 dark:text-accent-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100" x-text="t('title')"></h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1" x-text="t('subtitle')"></p>
            </div>

            @if($errors->has('duplicate'))
                <div class="mb-6 p-4 bg-accent-50 dark:bg-accent-900/20 border border-accent-500 dark:border-accent-500 rounded-xl">
                    <p class="text-sm text-accent-700 dark:text-accent-300 dark:text-accent-300 font-medium mb-2">
                        <svg class="w-4 h-4 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                        Account Already Exists
                    </p>
                    <p class="text-xs text-accent-700 dark:text-accent-300 dark:text-accent-400 mb-3">
                        {{ $errors->first('duplicate') }}
                        @if(session('duplicate_resident_name') && $errors->has('duplicate'))
                            <br>Matched record: <strong>{{ session('duplicate_resident_name') }}</strong>
                        @endif
                    </p>
                    <div class="flex flex-col gap-2">
                        <a href="{{ route('login') }}" wire:navigate class="btn-primary text-xs px-4 py-2 text-center">
                            Login with Existing Account
                        </a>
                        <form method="POST" action="{{ route('register.insist-duplicate') }}">
                            @csrf
                            <button type="submit" class="btn-ghost text-xs w-full text-accent-700 dark:text-accent-300 dark:text-accent-300 border border-accent-500 dark:border-accent-500 rounded-lg py-2">
                                I don't have an account &mdash; Submit for Staff Review
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="mb-10 max-w-xl mx-auto px-1">
                <div class="relative grid grid-cols-3 gap-1 sm:gap-3">
                    <div class="absolute top-5 left-[16.6667%] right-[16.6667%] h-0.5 bg-gray-200 dark:bg-gray-700 z-0"></div>
                    <div class="absolute top-5 left-[16.6667%] h-0.5 bg-primary-700 transition-all duration-500 ease-in-out z-0"
                        :style="`width: ${((step - 1) / (totalSteps - 1)) * 66.6667}%`">
                    </div>
                    <template x-for="s in totalSteps" :key="s">
                        <div class="relative z-10 flex flex-col items-center cursor-pointer" @click="goToStep(s)" role="button" :aria-label="'Step ' + s" :aria-current="s === step ? 'step' : null">
                            <div class="flex items-center justify-center w-9 h-9 sm:w-10 sm:h-10 rounded-full border-2 font-bold text-sm sm:text-base transition-all duration-300 shadow-md"
                                :class="{
                                    'bg-primary-700 border-primary-600 text-white ring-4 ring-primary-500/20': s === step,
                                    'bg-green-600 border-green-600 text-white': s < step,
                                    'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400': s > step
                                }">
                                <template x-if="s < step">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                </template>
                                <template x-if="s >= step">
                                    <span x-text="s"></span>
                                </template>
                            </div>
                            <span class="mt-2 w-full text-[11px] sm:text-xs font-semibold tracking-wide text-center leading-snug whitespace-normal transition-colors duration-300"
                                :class="{
                                    'text-primary-700 dark:text-primary-400 font-bold': s === step,
                                    'text-green-700 dark:text-green-400': s < step,
                                    'text-gray-600 dark:text-gray-400': s > step
                                }">
                                <span x-show="s === 1" x-text="t('personal_info_step')"></span>
                                <span x-show="s === 2" x-text="t('identity_step')"></span>
                                <span x-show="s === 3" x-text="t('document_step')"></span>
                            </span>
                        </div>
                    </template>
                </div>
            </div>

            <div x-cloak x-show="draftRestored" class="mb-4 p-3 bg-primary-50 dark:bg-primary-900/30 border border-primary-200 rounded-lg text-sm text-primary-700 dark:text-primary-400" x-text="t('draft_restored')"></div>

            <form id="registration-form" @keydown.enter="if ($event.target.matches('input, select, textarea')) $event.preventDefault()" @submit.prevent="submitForm($event)">
                @csrf
                <input type="hidden" name="assisted_mode" value="0">
                <input type="hidden" name="submission_id" x-bind:value="submissionId">

                <div x-show="step === 1" x-cloak>
                    <div class="flex items-center gap-3 mb-6">
                        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary-700 text-white text-sm font-bold">1</span>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100" x-text="t('step_1_title')"></h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="first_name" class="label"><span x-text="t('first_name')"></span> <span class="text-danger">*</span></label>
                            <input id="first_name" type="text" name="first_name" x-model="first_name" required
                                class="input-field uppercase-input @error('first_name') input-error @enderror"
                                placeholder="JUAN" autocomplete="given-name" @@input.debounce.500ms="saveDraft()">
                            @error('first_name') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400" x-show="errors.first_name" x-text="errors.first_name"></p>
                        </div>
                        <div>
                            <label for="last_name" class="label"><span x-text="t('last_name')"></span> <span class="text-danger">*</span></label>
                            <input id="last_name" type="text" name="last_name" x-model="last_name" required
                                class="input-field uppercase-input @error('last_name') input-error @enderror"
                                placeholder="DELA CRUZ" autocomplete="family-name" @@input.debounce.500ms="saveDraft()">
                            @error('last_name') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400" x-show="errors.last_name" x-text="errors.last_name"></p>
                        </div>
                        <div>
                            <label for="middle_name" class="label"><span x-text="t('middle_name')"></span></label>
                            <div class="flex items-center gap-2">
                                <input id="middle_name" type="text" name="middle_name" x-model="middle_name"
                                    :readonly="middleNameNone"
                                    class="input-field uppercase-input flex-1"
                                    placeholder="SANTOS" autocomplete="additional-name" @@input.debounce.500ms="saveDraft()">
                                <label class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400 whitespace-nowrap cursor-pointer">
                                    <input type="checkbox" name="middle_name_none" value="1" x-model="middleNameNone"
                                        @@change="if (middleNameNone) { middle_name = ''; } saveDraft()"
                                        class="w-4 h-4 rounded border-gray-200 dark:border-gray-700 text-primary-700">
                                    <span x-text="t('none') || 'N/A'"></span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label for="suffix" class="label"><span x-text="t('suffix')"></span></label>
                            <select id="suffix" name="suffix" x-model="suffix" class="select-field" autocomplete="honorific-suffix" @@change="saveDraft()">
                                <option value=""><span x-text="t('suffix')"></span></option>
                                <option value="JR.">JR.</option>
                                <option value="SR.">SR.</option>
                                <option value="II">II</option>
                                <option value="III">III</option>
                                <option value="IV">IV</option>
                                <option value="V">V</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="nationality" class="label"><span x-text="t('nationality')"></span> <span class="text-danger">*</span></label>
                            <input id="nationality" type="text" name="nationality" x-model="nationality" required
                                class="input-field uppercase-input" placeholder="FILIPINO" autocomplete="off"
                                @@input.debounce.500ms="saveDraft()">
                            @error('nationality') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400" x-show="errors.nationality" x-text="errors.nationality"></p>
                        </div>
                        <div>
                            <label for="occupation" class="label"><span x-text="t('occupation')"></span></label>
                            <input id="occupation" type="text" name="occupation" x-model="occupation"
                                class="input-field uppercase-input" placeholder="e.g., TEACHER" autocomplete="off"
                                @@input.debounce.500ms="saveDraft()">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="birthdate_month" class="label"><span x-text="t('birthdate')"></span> <span class="text-danger">*</span></label>
                            <div class="grid grid-cols-3 gap-1.5 sm:gap-2">
                                <select id="birthdate_month" name="birthdate_month" x-model="month" class="select-field" required autocomplete="bday-month" @@change="saveDraft()">
                                    <option value="" x-text="t('month')"></option>
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}">{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                                    @endforeach
                                </select>
                                <select id="birthdate_day" name="birthdate_day" x-model="day" class="select-field" required autocomplete="bday-day" @@change="saveDraft()">
                                    <option value="" x-text="t('day')"></option>
                                    @foreach(range(1, 31) as $d)
                                        <option value="{{ $d }}">{{ str_pad($d, 2, '0', STR_PAD_LEFT) }}</option>
                                    @endforeach
                                </select>
                                <select id="birthdate_year" name="birthdate_year" x-model="year" class="select-field" required autocomplete="bday-year" @@change="saveDraft()">
                                    <option value="" x-text="t('year')"></option>
                                    @foreach(range(date('Y'), 1950) as $y)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('birthdate_month') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            @error('birthdate_day') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            @error('birthdate_year') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400" x-show="errors.birthdate_month || errors.birthdate_day || errors.birthdate_year" x-text="errors.birthdate_month || errors.birthdate_day || errors.birthdate_year"></p>
                        </div>
                        <div>
                            <label for="age" class="label"><span x-text="t('age')"></span></label>
                            <input id="age" type="number" name="age" readonly
                                class="input-field bg-gray-50 dark:bg-gray-950 cursor-not-allowed" autocomplete="off"
                                x-bind:value="computedAge || ''" placeholder="Auto-computed">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="gender" class="label"><span x-text="t('sex')"></span> <span class="text-danger">*</span></label>
                            <select id="gender" name="gender" x-model="gender" class="select-field @error('gender') input-error @enderror" required autocomplete="sex" @@change="saveDraft()">
                                <option value="" x-text="t('select_sex')"></option>
                                <option value="male" x-text="t('male')"></option>
                                <option value="female" x-text="t('female')"></option>
                            </select>
                            @error('gender') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400" x-show="errors.gender" x-text="errors.gender"></p>
                        </div>
                        <div>
                            <label for="civil_status" class="label"><span x-text="t('civil_status')"></span> <span class="text-danger">*</span></label>
                            <select id="civil_status" name="civil_status" x-model="civil_status" class="select-field" autocomplete="off" @@change="saveDraft()">
                                <option value="" x-text="t('select_status')"></option>
                                <option value="single" x-text="t('single')"></option>
                                <option value="married" x-text="t('married')"></option>
                                <option value="widowed" x-text="t('widowed')"></option>
                                <option value="divorced" x-text="t('divorced')"></option>
                            </select>
                            @error('civil_status') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400" x-show="errors.civil_status" x-text="errors.civil_status"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="religion" class="label"><span x-text="t('religion')"></span></label>
                            <input id="religion" type="text" name="religion" x-model="religion"
                                class="input-field" placeholder="e.g., ROMAN CATHOLIC" autocomplete="off"
                                @@input.debounce.500ms="saveDraft()">
                        </div>
                        <div>
                            <label for="place_of_birth" class="label"><span x-text="t('place_of_birth')"></span> <span class="text-danger">*</span></label>
                            <input id="place_of_birth" type="text" name="place_of_birth" x-model="place_of_birth"
                                class="input-field uppercase-input" placeholder="e.g., BACOOR CITY, CAVITE" autocomplete="off"
                                @@input.debounce.500ms="saveDraft()">
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400" x-show="errors.place_of_birth" x-text="errors.place_of_birth"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label for="person_status" class="label font-semibold text-gray-900 dark:text-gray-100"><span x-text="t('person_status')"></span> <span class="text-danger">*</span></label>
                            <select id="person_status" name="person_status" x-model="personStatus" class="select-field" autocomplete="off" @@change="saveDraft(); errors.person_status = ''">
                                <option value="" x-text="t('person_status_none')"></option>
                                <option value="pwd" x-text="t('pwd')"></option>
                                <option value="senior" x-text="t('senior_citizen')"></option>
                                <option value="pregnant" x-text="t('pregnant')"></option>
                            </select>
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400" x-show="errors.person_status" x-text="errors.person_status"></p>
                        </div>
                        <div x-show="personStatus !== ''" x-cloak>
                            <label for="status_verification_photo" class="label" x-text="t('upload_verification_photo')"></label>
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400" x-show="errors.status_verification_photo" x-text="errors.status_verification_photo"></p>
                            <div class="mt-1">
                                <input id="status_verification_photo" type="file" name="status_verification_photo" accept=".jpg,.jpeg,.png,.pdf"
                                    class="hidden" autocomplete="off"
                                    @@change="onStatusPhotoChange($event)">
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" @@click="selectStatusPhoto('camera')"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg border transition-colors duration-200"
                                            :class="statusPhotoSource === 'camera' ? 'bg-primary-700 text-white border-primary-600' : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border-gray-200 dark:border-gray-700'">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span>{{ __('Take Photo') }}</span>
                                    </button>
                                    <button type="button" @@click="selectStatusPhoto('upload')"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg border transition-colors duration-200"
                                            :class="statusPhotoSource === 'upload' ? 'bg-primary-700 text-white border-primary-600' : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border-gray-200 dark:border-gray-700'">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 8l5-5 5 5m-5-5v12"/></svg>
                                        <span>{{ __('Upload') }}</span>
                                    </button>
                                </div>
                                <p class="mt-2 text-xs text-gray-600 dark:text-gray-400" x-text="t('upload_verification_hint')"></p>
                                <template x-if="statusPhotoPreview">
                                    <img :src="statusPhotoPreview" alt="Verification photo preview" class="mt-3 max-h-40 rounded-lg border border-gray-200 dark:border-gray-700 object-contain">
                                </template>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <input type="checkbox" name="is_pregnant" id="is_pregnant" value="1" x-model="isPregnant" @@change="saveDraft()" class="w-4 h-4 rounded border-gray-200 dark:border-gray-700 text-primary-700">
                            <label for="is_pregnant" class="text-sm text-gray-600 dark:text-gray-400" x-text="t('is_pregnant_label') || 'Currently pregnant'"></label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="label" x-text="t('address')"></label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div>
                                    <label for="building_no" class="text-xs text-gray-600 dark:text-gray-400" x-text="t('building_no')"></label>
                                    <input id="building_no" type="text" name="building_no" x-model="building_no"
                                        class="input-field uppercase-input w-full" placeholder="10" autocomplete="address-line1"
                                        @@input.debounce.500ms="saveDraft()">
                                </div>
                                <div>
                                    <label for="unit_no" class="text-xs text-gray-600 dark:text-gray-400" x-text="t('unit_no')"></label>
                                    <input id="unit_no" type="text" name="unit_no" x-model="unit_no"
                                        class="input-field uppercase-input w-full" placeholder="49" autocomplete="address-line2"
                                        @@input.debounce.500ms="saveDraft()">
                                </div>
                                <div>
                                    <label for="street" class="text-xs text-gray-600 dark:text-gray-400" x-text="t('street')"></label>
                                    <input id="street" type="text" name="street" x-model="street"
                                        class="input-field uppercase-input w-full" placeholder="e.g., M.H. DEL PILAR ST" autocomplete="street-address"
                                        @@input.debounce.500ms="saveDraft()">
                                </div>
                                <div>
                                    <label for="road" class="text-xs text-gray-600 dark:text-gray-400" x-text="t('road') || 'Road'"></label>
                                    <input id="road" type="text" name="road" x-model="road"
                                        class="input-field uppercase-input w-full" placeholder="e.g., MOLINO ROAD" autocomplete="off"
                                        @@input.debounce.500ms="saveDraft()">
                                </div>
                                <div>
                                    <label for="subdivision" class="text-xs text-gray-600 dark:text-gray-400" x-text="t('subdivision')"></label>
                                    <input id="subdivision" type="text" name="subdivision" x-model="subdivision"
                                        class="input-field uppercase-input w-full" placeholder="e.g., PHASE 1" autocomplete="address-line2"
                                        @@input.debounce.500ms="saveDraft()">
                                </div>
                                <div>
                                    <label for="barangay" class="text-xs text-gray-600 dark:text-gray-400" x-text="t('barangay')"></label>
                                    <input id="barangay" type="text" name="barangay" x-model="barangay"
                                        class="input-field uppercase-input w-full" placeholder="MOLINO I" autocomplete="address-level2"
                                        @@input.debounce.500ms="saveDraft()">
                                </div>
                                <div>
                                    <label for="purok" class="text-xs text-gray-600 dark:text-gray-400" x-text="t('purok')"></label>
                                    <input id="purok" type="text" name="purok" x-model="purok"
                                        class="input-field uppercase-input w-full" placeholder="e.g., PUROK 1" autocomplete="off"
                                        @@input.debounce.500ms="saveDraft()">
                                </div>
                            </div>
                            <input type="hidden" name="city" value="BACOOR CITY">
                            <input type="hidden" name="province" value="CAVITE">
                            <input type="hidden" name="zip_code" value="4102">
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                <span x-text="t('default_location') || 'Default: '"></span> <strong>BACOOR CITY, CAVITE 4102</strong>
                            </p>
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400" x-show="errors.street || errors.barangay" x-text="errors.street || errors.barangay"></p>
                            <div class="mt-3 p-3 bg-gray-50 dark:bg-gray-950 rounded-xl text-sm text-gray-600 dark:text-gray-400">
                                <span class="font-medium" x-text="t('complete_address')"></span><br>
                                <span x-text="fullAddress"></span>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label for="contact_number" class="label"><span x-text="t('contact_number')"></span> <span class="text-danger">*</span></label>
                                <input id="contact_number" type="tel" name="contact_number" x-model="contactNumber" required
                                    class="input-field phone-mask @error('contact_number') input-error @enderror"
                                    placeholder="09XX-XXX-XXXX" maxlength="13" autocomplete="tel"
                                    @@input="formatPhone($el); saveDraft()">
                                <p class="mt-1 text-xs text-gray-600 dark:text-gray-400" x-text="t('contact_format')"></p>
                                @error('contact_number') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400" x-show="errors.contact_number" x-text="errors.contact_number"></p>
                            </div>
                            <div>
                                <label for="emergency_contact" class="label"><span x-text="t('emergency_contact')"></span> <span class="text-danger">*</span></label>
                                <input id="emergency_contact" type="tel" name="emergency_contact" x-model="emergencyContact" required
                                    class="input-field phone-mask @error('emergency_contact') input-error @enderror"
                                    placeholder="09XX-XXX-XXXX" maxlength="13" autocomplete="off"
                                    @@input="formatPhone($el); saveDraft()">
                                <p class="mt-1 text-xs text-gray-600 dark:text-gray-400" x-text="t('contact_format')"></p>
                                @error('emergency_contact') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400" x-show="errors.emergency_contact" x-text="errors.emergency_contact"></p>
                                <template x-if="contactNumber && emergencyContact && contactNumber === emergencyContact">
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400" x-text="t('emergency_not_equal')"></p>
                                </template>
                            </div>
                            <div>
                                <label for="email" class="label"><span x-text="t('email_address')"></span></label>
                                <input id="email" type="email" name="email" x-model="email"
                                    class="input-field" placeholder="juan@example.com" autocomplete="email"
                                    @@input.debounce.500ms="saveDraft()">
                                @error('email') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400" x-show="errors.email" x-text="errors.email"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-200 dark:border-gray-700" x-show="step === 1" x-cloak>

                <div x-show="step === 2" x-cloak>
                    <div class="flex items-center gap-3 mb-6">
                        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary-700 text-white text-sm font-bold">2</span>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100" x-text="t('step_2_title')"></h2>
                    </div>

                    <div class="mb-6 p-4 bg-primary-50 dark:bg-primary-900/30 border border-primary-200 dark:border-primary-800 rounded-xl text-sm text-primary-700 dark:text-primary-400">
                        <p class="font-medium mb-2" x-text="t('id_scan_how_it_works')"></p>
                        <ol class="list-decimal list-inside space-y-1">
                            <li x-text="t('id_scan_step_1')"></li>
                            <li x-text="t('id_scan_step_2')"></li>
                            <li x-text="t('id_scan_step_3')"></li>
                        </ol>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="id_type" class="label"><span x-text="t('id_type')"></span> <span class="text-danger">*</span></label>
                            <select id="id_type" name="id_type" x-model="idType" class="select-field @error('id_type') input-error @enderror" required autocomplete="off"
                                    @@change="onIdTypeChange()">
                                <option value="" x-text="t('select_id_type')"></option>
                                <option value="phil_id">Philippine National ID (PhilID/ePhilID)</option>
                                <option value="passport">Philippine Passport</option>
                                <option value="umid">UMID</option>
                                <option value="philhealth">PhilHealth</option>
                                <option value="drivers_license">Driver's License</option>
                                <option value="prc_id">PRC ID</option>
                                <option value="postal_id">Postal ID</option>
                                <option value="sss_id">SSS ID</option>
                                <option value="tin_id">TIN ID</option>
                                <option value="ibp_id">IBP ID</option>
                                <option value="owwa_ofw_id">OWWA/OFW ID</option>
                                <option value="barangay_id">Barangay ID</option>
                                <option value="school_id">School ID</option>
                            </select>
                            @error('id_type') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400" x-show="errors.id_type" x-text="errors.id_type"></p>
                        </div>
                    </div>

                    <div id="id-scan-section">
                    <template x-if="idType">
                    <div class="mt-6 space-y-6">
                        <div class="p-4 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl">
                            <label class="label font-semibold text-gray-900 dark:text-gray-100" x-text="t('id_scan_front')"> <span class="text-danger">*</span></label>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-3" x-text="t('id_scan_front_hint')"></p>
                            <template x-if="idScanFrontNeedsReupload">
                                <div class="mb-3 p-3 bg-accent-50 dark:bg-accent-900/20 border border-accent-200 dark:border-accent-800 rounded-lg text-sm text-accent-700 dark:text-accent-300">
                                    <span x-text="t('id_scan_reupload_required')"></span>
                                </div>
                            </template>
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400" x-show="errors.id_scan_front" x-text="errors.id_scan_front"></p>

                            <div class="flex items-center gap-4">
                                <div class="flex-1">
                                    <input id="id_scan_front" type="file" name="id_scan_front" accept=".jpg,.jpeg,.png"
                                        class="hidden" autocomplete="off"
                                        @@change="onIdScanChange('front', $event)">
                                    <div class="flex flex-wrap gap-2">
                                        <button type="button" @@click="selectIdPhoto('front', 'camera')"
                                                class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg border transition-colors duration-200"
                                                :class="idScanFrontSource === 'camera' ? 'bg-primary-700 text-white border-primary-600' : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border-gray-200 dark:border-gray-700'">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span>{{ __('Take Photo') }}</span>
                                        </button>
                                        <button type="button" @@click="selectIdPhoto('front', 'upload')"
                                                class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg border transition-colors duration-200"
                                                :class="idScanFrontSource === 'upload' ? 'bg-primary-700 text-white border-primary-600' : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border-gray-200 dark:border-gray-700'">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 8l5-5 5 5m-5-5v12"/></svg>
                                            <span>{{ __('Upload') }}</span>
                                        </button>
                                    </div>
                                    <p class="mt-2 text-xs text-gray-600 dark:text-gray-400">Accepted: JPG, JPEG, PNG</p>
                                    <template x-if="idScanFrontPreview">
                                        <img :src="idScanFrontPreview" alt="ID front preview" class="mt-3 max-h-48 rounded-lg border border-gray-200 dark:border-gray-700 object-contain">
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl">
                            <label class="label font-semibold text-gray-900 dark:text-gray-100" x-text="t('id_scan_back')"> <span class="text-danger">*</span></label>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-3" x-text="t('id_scan_back_hint')"></p>
                            <template x-if="idScanBackNeedsReupload">
                                <div class="mb-3 p-3 bg-accent-50 dark:bg-accent-900/20 border border-accent-200 dark:border-accent-800 rounded-lg text-sm text-accent-700 dark:text-accent-300">
                                    <span x-text="t('id_scan_reupload_required')"></span>
                                </div>
                            </template>
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400" x-show="errors.id_scan_back" x-text="errors.id_scan_back"></p>

                            <div class="flex items-center gap-4">
                                <div class="flex-1">
                                    <input id="id_scan_back" type="file" name="id_scan_back" accept=".jpg,.jpeg,.png"
                                        class="hidden" autocomplete="off"
                                        @@change="onIdScanChange('back', $event)">
                                    <div class="flex flex-wrap gap-2">
                                        <button type="button" @@click="selectIdPhoto('back', 'camera')"
                                                class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg border transition-colors duration-200"
                                                :class="idScanBackSource === 'camera' ? 'bg-primary-700 text-white border-primary-600' : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border-gray-200 dark:border-gray-700'">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span>{{ __('Take Photo') }}</span>
                                        </button>
                                        <button type="button" @@click="selectIdPhoto('back', 'upload')"
                                                class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg border transition-colors duration-200"
                                                :class="idScanBackSource === 'upload' ? 'bg-primary-700 text-white border-primary-600' : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border-gray-200 dark:border-gray-700'">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 8l5-5 5 5m-5-5v12"/></svg>
                                            <span>{{ __('Upload') }}</span>
                                        </button>
                                    </div>
                                    <p class="mt-2 text-xs text-gray-600 dark:text-gray-400">Accepted: JPG, JPEG, PNG</p>
                                    <template x-if="idScanBackPreview">
                                        <img :src="idScanBackPreview" alt="ID back preview" class="mt-3 max-h-48 rounded-lg border border-gray-200 dark:border-gray-700 object-contain">
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                    </template>

<div x-show="!idType" x-cloak class="mt-6 p-4 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl text-sm text-gray-600 dark:text-gray-400">
                        <p class="flex items-center gap-2">
                            <svg class="w-5 h-5 shrink-0 text-primary-700 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span x-text="t('id_scan_pending_type')"></span>
                        </p>
                    </div>
                    <div x-show="idType" x-cloak class="mt-6 p-4 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary-700 text-white text-sm font-bold">2.2</span>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="t('id_1x1_step')"></h3>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mb-3" x-text="t('id_1x1_step_hint')"></p>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" @@click="selectId1x1('camera')"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg border transition-colors duration-200"
                                    :class="id1x1Source === 'camera' ? 'bg-primary-700 text-white border-primary-600' : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border-gray-200 dark:border-gray-700'">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span x-text="t('take_photo') || 'Take Photo'"></span>
                            </button>
                            <button type="button" @@click="selectId1x1('upload')"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg border transition-colors duration-200"
                                    :class="id1x1Source === 'upload' ? 'bg-primary-700 text-white border-primary-600' : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border-gray-200 dark:border-gray-700'">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 8l5-5 5 5m-5-5v12"/></svg>
                                <span x-text="t('upload') || 'Upload'"></span>
                            </button>
                        </div>
                        <p class="mt-2 text-xs text-gray-600 dark:text-gray-400">Accepted: JPG, JPEG, PNG (1x1)</p>
                        <template x-if="id1x1Preview">
                            <img :src="id1x1Preview" alt="1x1 ID preview" class="mt-3 max-h-32 rounded-lg border border-gray-200 dark:border-gray-700 object-contain">
                        </template>
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400" x-show="errors.id_1x1" x-text="errors.id_1x1"></p>
                    </div>
                </div>
                <input type="file" name="id_1x1" id="id_1x1_file" accept=".jpg,.jpeg,.png" class="hidden"
                    @@change="onId1x1Change($event)">
                <hr class="border-gray-200 dark:border-gray-700" x-show="step === 2" x-cloak>
            </div>

                <div x-show="step === 3" x-cloak>
                    <div class="flex items-center gap-3 mb-6">
                        <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary-700 text-white text-sm font-bold">3</span>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100" x-text="t('step_3_title')"></h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="document_type_id" class="label"><span x-text="t('document_type')"></span> <span class="text-danger">*</span></label>
                            <select id="document_type_id" name="document_type_id" class="select-field" required autocomplete="off" x-model="documentTypeId" @@change="onDocumentTypeChange()">
                                <option value="" x-text="t('select_document')"></option>
                                <option value="{{ $documentTypes->firstWhere('code', 'CERT_INDIGENCY')?->id }}" {{ old('document_type_id') == ($documentTypes->firstWhere('code', 'CERT_INDIGENCY')?->id) ? 'selected' : '' }}>
                                    {{ $documentTypes->firstWhere('code', 'CERT_INDIGENCY')?->name ?? 'Certificate of Indigency' }}
                                </option>
                                <option value="{{ $documentTypes->firstWhere('code', 'CERT_BARANGAY_CERT')?->id }}" {{ old('document_type_id') == ($documentTypes->firstWhere('code', 'CERT_BARANGAY_CERT')?->id) ? 'selected' : '' }}>
                                    {{ $documentTypes->firstWhere('code', 'CERT_BARANGAY_CERT')?->name ?? 'Barangay Certificate' }}
                                </option>
                                <option value="{{ $documentTypes->firstWhere('code', 'CERT_RESIDENCY')?->id }}" {{ old('document_type_id') == ($documentTypes->firstWhere('code', 'CERT_RESIDENCY')?->id) ? 'selected' : '' }}>
                                    {{ $documentTypes->firstWhere('code', 'CERT_RESIDENCY')?->name ?? 'Certificate of Residency' }}
                                </option>
                                <option value="{{ $documentTypes->firstWhere('code', 'BRGY_CLEARANCE')?->id }}" {{ old('document_type_id') == ($documentTypes->firstWhere('code', 'BRGY_CLEARANCE')?->id) ? 'selected' : '' }}>
                                    {{ $documentTypes->firstWhere('code', 'BRGY_CLEARANCE')?->name ?? 'Barangay Clearance' }}
                                </option>
                            </select>
                            @error('document_type_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400" x-show="errors.document_type_id" x-text="errors.document_type_id"></p>
                        </div>
                    </div>

                    <div x-show="isCertificateOfIndigency()" x-cloak class="mt-4">
                        <label for="purpose_id" class="label"><span x-text="t('purpose')"></span> <span class="text-danger">*</span></label>
                        <select id="purpose_id" name="purpose_id" class="select-field" required autocomplete="off" x-model="purposeId" @@change="saveDraft()">
                            <option value="" x-text="t('select_purpose')"></option>
                            @foreach($purposes as $purpose)
                                @if($purpose->code === 'MEDICAL_ASSISTANCE' || $purpose->code === 'FINANCIAL_ASSISTANCE')
                                    <option value="{{ $purpose->id }}" {{ old('purpose_id') == $purpose->id ? 'selected' : '' }} x-text="t('purpose_'.$purpose->code)"></option>
                                @endif
                            @endforeach
                        </select>
                        @error('purpose_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div x-show="isCertificateOfBarangayCert()" x-cloak class="mt-4">
                        <label for="purpose_id" class="label"><span x-text="t('purpose')"></span> <span class="text-danger">*</span></label>
                        <select id="purpose_id" name="purpose_id" class="select-field" required autocomplete="off" x-model="purposeId" @@change="saveDraft()">
                            <option value="" x-text="t('select_purpose')"></option>
                            @foreach($purposes as $purpose)
                                @if($purpose->code === 'ENROLLMENT' || $purpose->code === 'SCHOLARSHIP' || $purpose->code === 'VOUCHER')
                                    <option value="{{ $purpose->id }}" {{ old('purpose_id') == $purpose->id ? 'selected' : '' }} x-text="t('purpose_'.$purpose->code)"></option>
                                @endif
                            @endforeach
                        </select>
                        @error('purpose_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div x-show="isCertificateOfResidency()" x-cloak class="mt-4">
                        <label for="purpose_other" class="label">{{ __('registration.purpose_other') }} <span class="text-danger">*</span></label>
                        <input id="purpose_other" type="text" name="purpose_other" x-model="purposeOther" required
                            class="input-field uppercase-input" placeholder="{{ __('registration.purpose_other_placeholder') }}" autocomplete="off"
                            @@input.debounce.500ms="saveDraft()">
                        @error('purpose_other') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div x-show="isBarangayClearance()" x-cloak class="mt-4">
                        <label for="purpose_id" class="label"><span x-text="t('purpose')"></span> <span class="text-danger">*</span></label>
                        <select id="purpose_id" name="purpose_id" class="select-field" required autocomplete="off" x-model="purposeId" @@change="saveDraft()">
                            <option value="" x-text="t('select_purpose')"></option>
                            @foreach($purposes as $purpose)
                                @if(in_array($purpose->code, ['LOCAL_EMPLOYMENT', 'NBI_REQUIREMENT', 'POLICE_CLEARANCE', 'POSTAL_ID', 'BANK_LOAN', 'MARRIAGE']))
                                    <option value="{{ $purpose->id }}" {{ old('purpose_id') == $purpose->id ? 'selected' : '' }} x-text="t('purpose_'.$purpose->code)"></option>
                                @endif
                            @endforeach
                        </select>
                        @error('purpose_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-950 rounded-xl">
                        <x-checkbox name="privacy_consent" :label="__('registration.privacy_consent')" required :error="$errors->first('privacy_consent')" x-model="privacyConsent" />
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400" x-show="errors.privacy_consent" x-text="errors.privacy_consent"></p>
                    </div>

                    <div class="mt-8 p-4 bg-gray-50 dark:bg-gray-950 rounded-xl">
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <h3 class="font-semibold text-sm" x-text="t('personal_info_step')"></h3>
                            <button type="button" @@click="step = 1" class="text-xs text-primary-700 dark:text-primary-400 hover:underline" x-text="t('edit')"></button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2 text-sm">
                            <div><span class="text-gray-600 dark:text-gray-400" x-text="t('name')"></span><p class="font-medium" x-text="fullName"></p></div>
                            <div><span class="text-gray-600 dark:text-gray-400" x-text="t('sex')"></span><p class="font-medium capitalize" x-text="genderLabel"></p></div>
                            <div><span class="text-gray-600 dark:text-gray-400" x-text="t('birthdate')"></span><p class="font-medium" x-text="birthdateLabel"></p></div>
                            <div><span class="text-gray-600 dark:text-gray-400" x-text="t('civil_status')"></span><p class="font-medium capitalize" x-text="civilStatusLabel"></p></div>
                            <div><span class="text-gray-600 dark:text-gray-400" x-text="t('nationality')"></span><p class="font-medium" x-text="nationality || '-'"></p></div>
                            <div><span class="text-gray-600 dark:text-gray-400" x-text="t('religion')"></span><p class="font-medium" x-text="religion || '-'"></p></div>
                            <div><span class="text-gray-600 dark:text-gray-400" x-text="t('place_of_birth')"></span><p class="font-medium" x-text="place_of_birth || '-'"></p></div>
                            <div><span class="text-gray-600 dark:text-gray-400" x-text="t('person_status')"></span><p class="font-medium" x-text="personStatusLabel"></p></div>
                        </div>
                    </div>

                    <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-950 rounded-xl">
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <h3 class="font-semibold text-sm" x-text="t('address')"></h3>
                            <button type="button" @@click="step = 1" class="text-xs text-primary-700 dark:text-primary-400 hover:underline" x-text="t('edit')"></button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2 text-sm">
                            <div class="sm:col-span-2"><span class="text-gray-600 dark:text-gray-400" x-text="t('address')"></span><p class="font-medium" x-text="fullAddress"></p></div>
                            <div><span class="text-gray-600 dark:text-gray-400" x-text="t('contact_number')"></span><p class="font-medium" x-text="contactNumber || '-'"></p></div>
                            <div><span class="text-gray-600 dark:text-gray-400" x-text="t('emergency_contact')"></span><p class="font-medium" x-text="emergencyContact || '-'"></p></div>
                            <div><span class="text-gray-600 dark:text-gray-400" x-text="t('email_address')"></span><p class="font-medium" x-text="email || '-'"></p></div>
                        </div>
                    </div>

                    <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-950 rounded-xl">
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <h3 class="font-semibold text-sm" x-text="t('identity_step')"></h3>
                            <button type="button" @@click="step = 2" class="text-xs text-primary-700 dark:text-primary-400 hover:underline" x-text="t('edit')"></button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2 text-sm">
                            <div><span class="text-gray-600 dark:text-gray-400" x-text="t('id_type')"></span><p class="font-medium capitalize" x-text="idType ? idType.replace(/_/g, ' ') : '-'"></p></div>
                            <div>
                                <span class="text-gray-600 dark:text-gray-400" x-text="t('id_scan_front')"></span>
                                <p class="font-medium">
                                    <span x-show="idScanFrontPreview" class="text-green-600 dark:text-green-400" x-text="t('uploaded')"></span>
                                    <span x-show="!idScanFrontPreview" x-text="t('not_uploaded')"></span>
                                </p>
                            </div>
                            <div>
                                <span class="text-gray-600 dark:text-gray-400" x-text="t('id_scan_back')"></span>
                                <p class="font-medium">
                                    <span x-show="idScanBackPreview" class="text-green-600 dark:text-green-400" x-text="t('uploaded')"></span>
                                    <span x-show="!idScanBackPreview" x-text="t('not_uploaded')"></span>
                                </p>
                            </div>
                            <div>
                                <span class="text-gray-600 dark:text-gray-400" x-text="t('id_photo_1x1')"></span>
                                <p class="font-medium">
                                    <span x-show="id1x1Preview" class="text-green-600 dark:text-green-400" x-text="t('uploaded')"></span>
                                    <span x-show="!id1x1Preview" x-text="t('not_uploaded')"></span>
                                </p>
                            </div>
                            <template x-if="personStatus !== ''">
                                <div>
                                    <span class="text-gray-600 dark:text-gray-400" x-text="t('upload_verification_photo')"></span>
                                    <p class="font-medium">
                                        <span x-show="statusPhotoPreview" class="text-green-600 dark:text-green-400" x-text="t('uploaded')"></span>
                                        <span x-show="!statusPhotoPreview" x-text="t('not_uploaded')"></span>
                                    </p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-950 rounded-xl">
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <h3 class="font-semibold text-sm" x-text="t('document_type')"></h3>
                            <button type="button" @@click="goToStep(3); scrollToField('document_type_id')" class="text-xs text-primary-700 dark:text-primary-400 hover:underline" x-text="t('edit')"></button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2 text-sm">
                            <div>
                                <span class="text-gray-600 dark:text-gray-400" x-text="t('document_type')"></span>
                                <p class="font-medium capitalize" x-text="documentTypeName || '-'"></p>
                            </div>
                            <div>
                                <span class="text-gray-600 dark:text-gray-400" x-text="t('purpose')"></span>
                                <p class="font-medium" x-text="purposeName || '-'"></p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-950 rounded-xl" x-show="controlNumber">
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <h3 class="font-semibold text-sm" x-text="t('reference')"></h3>
                            <button type="button" @@click="goToStep(3); scrollToField('document_type_id')" class="text-xs text-primary-700 dark:text-primary-400 hover:underline" x-text="t('edit')"></button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2 text-sm">
                            <div>
                                <span class="text-gray-600 dark:text-gray-400" x-text="t('control_number')"></span>
                                <p class="font-medium text-primary-700 dark:text-primary-400" x-text="controlNumber || '-'"></p>
                            </div>
                            <div>
                                <span class="text-gray-600 dark:text-gray-400" x-text="t('queue_number')"></span>
                                <p class="font-medium" x-text="queueNumber || '-'"></p>
                            </div>
                            <div class="sm:col-span-2">
                                <span class="text-gray-600 dark:text-gray-400" x-text="t('qr_code')"></span>
                                <div class="mt-1" x-show="qrCode">
                                    <img x-bind:src="'data:image/png;base64,' + qrCode" alt="QR Code" class="w-32 h-32">
                                </div>
                            </div>
                        </div>
                    </div>
</div>

                    <div x-cloak x-show="formError" class="mb-3 p-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl text-sm text-red-700 dark:text-red-400" role="alert">
                        <svg class="w-4 h-4 inline mr-1 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
                        <span x-text="formError"></span>
                    </div>

                    <div x-cloak x-show="submitting" class="mb-3 flex items-center gap-2 p-3 bg-primary-50 dark:bg-primary-900/30 border border-primary-200 dark:border-primary-800 rounded-xl text-sm text-primary-700 dark:text-primary-400" role="status">
                        <svg class="w-4 h-4 animate-spin shrink-0" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span x-text="t('processing') || 'Processing your request, please wait...'"></span>
                    </div>

                    <div class="flex flex-wrap items-center justify-between gap-3 pt-4">
                        <button type="button" x-show="step > 1" x-cloak @@click="goToStep(this.step - 1)" :disabled="submitting" class="btn-ghost">
                            <svg class="w-5 h-5 mr-1 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            <span x-text="t('back') || 'Back'"></span>
                        </button>
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" @@click="clearForm()" :disabled="submitting" class="btn-ghost border border-danger/40 text-red-600 dark:text-red-400 hover:bg-red-600 hover:text-white text-xs px-4 py-2 rounded-lg transition-colors duration-200" x-text="t('clear_form')"></button>
                            <a href="{{ url('/') }}" wire:navigate :class="submitting ? 'pointer-events-none opacity-50' : ''" class="btn-ghost" x-text="t('cancel')"></a>
                            <template x-if="step < totalSteps">
                                <button type="button" @@click="validateStep()" :disabled="submitting" class="btn-primary px-8" x-text="t('next') || 'Next'"></button>
                            </template>
                            <template x-if="step === totalSteps">
                                <button type="submit" class="btn-primary px-8" :disabled="submitting">
                                    <svg class="w-5 h-5 mr-2 inline" x-show="!submitting" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    <svg class="w-5 h-5 mr-2 inline animate-spin" x-show="submitting" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                    <span x-text="submitting ? t('submitting') : t('submit_request')"></span>
                                </button>
                            </template>
                        </div>
                    </div>
            </form>
        </x-card>
    </div>
</div>

<script>
    function registrationForm(translations) {
        return {
            lang: localStorage.getItem('registration_lang') || 'fil',
            translations: translations,

            step: 1,
            totalSteps: 3,
            submitting: false,
            errors: {},
            formError: '',
            draftRestored: false,
            submissionId: (window.crypto && typeof window.crypto.randomUUID === 'function')
                ? window.crypto.randomUUID()
                : 'sub-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 10),

            first_name: '{{ old("first_name") }}',
            last_name: '{{ old("last_name") }}',
            middle_name: '{{ old("middle_name") }}',
            middleNameNone: {{ old('middle_name_none') ? 'true' : 'false' }},
            suffix: '{{ old("suffix") }}',
            nationality: '{{ old("nationality", "FILIPINO") }}',
            occupation: '{{ old("occupation") }}',

            month: '{{ old("birthdate_month") }}',
            day: '{{ old("birthdate_day") }}',
            year: '{{ old("birthdate_year") }}',

            gender: '{{ old("gender") }}',
            civil_status: '{{ old("civil_status") }}',

            religion: '{{ old("religion") }}',
            place_of_birth: '{{ old("place_of_birth") }}',

            personStatus: '{{ old("person_status") }}',

            building_no: '{{ old("building_no") }}',
            unit_no: '{{ old("unit_no") }}',
            street: '{{ old("street") }}',
            road: '{{ old("road") }}',
            barangay: '{{ old("barangay", "MOLINO I") }}',
            subdivision: '{{ old("subdivision") }}',
            purok: '{{ old("purok") }}',

            contactNumber: '{{ old("contact_number") }}',
            emergencyContact: '{{ old("emergency_contact") }}',
            email: '{{ old("email") }}',

            idType: '{{ old("id_type") }}',

            idScanFrontPreview: '',
            idScanFrontSource: 'upload',
            idScanFrontSelected: false,
            idScanFrontNeedsReupload: false,
            idScanBackPreview: '',
            idScanBackSource: 'upload',
            idScanBackSelected: false,
            idScanBackNeedsReupload: false,
            statusPhotoPreview: '',
            statusPhotoSource: 'upload',
            id1x1Preview: '',
            id1x1Source: 'upload',
            id1x1Selected: false,
            id1x1NeedsReupload: false,

            documentTypeId: '{{ old("document_type_id") }}',
            purposeId: '{{ old("purpose_id") }}',
            purposeOther: '{{ old("purpose_other") }}',
            privacyConsent: {{ old('privacy_consent') ? 'true' : 'false' }},
            isPregnant: {{ old('is_pregnant') ? 'true' : 'false' }},
            controlNumber: '',
            qrCode: '',
            queueNumber: '',

            documentTypeNames: {{ Illuminate\Support\Js::from($documentTypes->pluck('name', 'id')) }},
            purposeNames: {{ Illuminate\Support\Js::from($purposes->pluck('name', 'id')) }},

            fieldStepMap: {
                first_name: 1, last_name: 1, middle_name: 1, middle_name_none: 1,
                suffix: 1, nationality: 1, occupation: 1,
                birthdate_month: 1, birthdate_day: 1, birthdate_year: 1,
                gender: 1, civil_status: 1, religion: 1, place_of_birth: 1,
                person_status: 1, status_verification_photo: 1,
                building_no: 1, unit_no: 1, street: 1, road: 1,
                barangay: 1, subdivision: 1, purok: 1,
                contact_number: 1, emergency_contact: 1, email: 1,
                city: 1, province: 1,
                id_type: 2, id_scan_front: 2, id_scan_back: 2, id_1x1: 2,
                document_type_id: 3, purpose_id: 3, purpose_other: 3,
                privacy_consent: 3, is_pregnant: 3,
            },

            get computedAge() {
                if (!this.month || !this.day || !this.year) return '';
                const birth = new Date(this.year, this.month - 1, this.day);
                const today = new Date();
                let age = today.getFullYear() - birth.getFullYear();
                const m = today.getMonth() - birth.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--;
                return age >= 0 ? age : '';
            },

            get fullAddress() {
                const parts = [];
                if (this.building_no) parts.push('BLDG ' + this.building_no);
                if (this.unit_no) parts.push('UNIT ' + this.unit_no);
                if (this.street) parts.push(this.street);
                if (this.road) parts.push(this.road);
                if (this.subdivision) parts.push(this.subdivision);
                if (this.barangay) parts.push(this.barangay);
                if (this.purok) parts.push(this.purok);
                parts.push('BACOOR CITY, CAVITE 4102');
                return parts.join(', ');
            },

            get fullName() {
                let name = (this.first_name || '').trim();
                if (this.middle_name) name += ' ' + this.middle_name.trim();
                name += ' ' + (this.last_name || '').trim();
                if (this.suffix) name += ' ' + this.suffix;
                return name.trim();
            },

            get genderLabel() {
                return this.gender ? this.t(this.gender) : '-';
            },

            get civilStatusLabel() {
                return this.civil_status ? this.t(this.civil_status) : '-';
            },

            get personStatusLabel() {
                if (!this.personStatus) return this.t('person_status_none');
                return this.t(this.personStatus === 'senior' ? 'senior_citizen' : this.personStatus);
            },

            get birthdateLabel() {
                if (!this.month || !this.day || !this.year) return '-';
                let label = this.month + '-' + this.day + '-' + this.year;
                if (this.computedAge) label += ' (' + this.computedAge + ')';
                return label;
            },

            get documentTypeName() {
                return this.documentTypeNames[this.documentTypeId] || '-';
            },

            get purposeName() {
                return this.purposeNames[this.purposeId] || '-';
            },

            get purposeIsOthers() {
                return false;
            },

            isCertificateOfIndigency() {
                return this.documentTypeId == this.getDocumentTypeIdByCode('CERT_INDIGENCY');
            },
            isCertificateOfBarangayCert() {
                return this.documentTypeId == this.getDocumentTypeIdByCode('CERT_BARANGAY_CERT');
            },
            isCertificateOfResidency() {
                return this.documentTypeId == this.getDocumentTypeIdByCode('CERT_RESIDENCY');
            },
            isBarangayClearance() {
                return this.documentTypeId == this.getDocumentTypeIdByCode('BRGY_CLEARANCE');
            },
            getDocumentTypeIdByCode(code) {
                var ids = @json($documentTypeCodeMap);
                return ids[code] || null;
            },
            onDocumentTypeChange() {
                this.purposeId = '';
                this.purposeOther = '';
                this.saveDraft();
            },

            t(key) {
                return (this.translations[this.lang] && this.translations[this.lang][key]) || key;
            },

            toggleLang() {
                this.lang = this.lang === 'en' ? 'fil' : 'en';
                localStorage.setItem('registration_lang', this.lang);
            },

            init() {
                this.restoreDraft();
                this.$nextTick(() => {
                    document.getElementById('registration-form')?.addEventListener('input', () => this.saveDraft());
                });
                window.addEventListener('pageshow', (e) => {
                    if (e.persisted) {
                        this.restoreDraft();
                    }
                });
            },

            formatPhone(el) {
                let digits = el.value.replace(/\D/g, '').slice(0, 11);
                let formatted = '';
                if (digits.length > 0) {
                    formatted = digits.slice(0, 4);
                    if (digits.length > 4) formatted += '-' + digits.slice(4, 7);
                    if (digits.length > 7) formatted += '-' + digits.slice(7, 11);
                }
                el.value = formatted;
            },

            selectIdPhoto(side, source) {
                const key = 'idScan' + (side === 'back' ? 'Back' : 'Front');
                this[key + 'Source'] = source;
                const input = document.getElementById('id_scan_' + side);
                if (input) {
                    input.removeAttribute('capture');
                    if (source === 'camera') input.setAttribute('capture', 'environment');
                    input.value = '';
                    this[key + 'Preview'] = '';
                    this[key + 'Selected'] = false;
                    input.click();
                }
            },

            onIdScanChange(side, event) {
                const file = event.target.files[0];
                const key = 'idScan' + (side === 'back' ? 'Back' : 'Front');
                this[key + 'Preview'] = file ? URL.createObjectURL(file) : '';
                this[key + 'Selected'] = !!file;
                this[key + 'NeedsReupload'] = false;
                this.errors['id_scan_' + side] = '';
                this.saveDraft();
            },

            selectStatusPhoto(source) {
                this.statusPhotoSource = source;
                const input = document.getElementById('status_verification_photo');
                if (input) {
                    input.removeAttribute('capture');
                    if (source === 'camera') input.setAttribute('capture', 'environment');
                    input.value = '';
                    this.statusPhotoPreview = '';
                    input.click();
                }
            },

            onStatusPhotoChange(event) {
                const file = event.target.files[0];
                this.statusPhotoPreview = file ? URL.createObjectURL(file) : '';
                this.saveDraft();
                this.errors.status_verification_photo = '';
            },

            selectId1x1(source) {
                this.id1x1Source = source;
                const input = document.getElementById('id_1x1_file');
                if (input) {
                    input.removeAttribute('capture');
                    if (source === 'camera') input.setAttribute('capture', 'environment');
                    input.value = '';
                    this.id1x1Preview = '';
                    this.id1x1Selected = false;
                    input.click();
                }
            },

            onId1x1Change(event) {
                const file = event.target.files[0];
                this.id1x1Preview = file ? URL.createObjectURL(file) : '';
                this.id1x1Selected = !!file;
                this.id1x1NeedsReupload = false;
                this.errors.id_1x1 = '';
                this.saveDraft();
            },

            goToStep(n) {
                if (this.submitting) return;
                const target = Number(n);
                if (Number.isInteger(target) && target >= 1 && target <= this.totalSteps) {
                    this.step = target;
                    this.saveDraft();
                }
            },

            validateStep() {
                let valid = true;
                const errors = {};
                let firstErrorField = null;
                this.formError = '';
                if (this.step === 1) {
                    if (!this.first_name) { valid = false; errors.first_name = this.t('first_name_required') || 'First name is required.'; if (!firstErrorField) firstErrorField = 'first_name'; }
                    if (!this.last_name) { valid = false; errors.last_name = this.t('last_name_required') || 'Last name is required.'; if (!firstErrorField) firstErrorField = 'last_name'; }
                    if (!this.middle_name && !this.middleNameNone) { valid = false; errors.middle_name = this.t('middle_name_required') || 'Middle name is required.'; if (!firstErrorField) firstErrorField = 'middle_name'; }
                    if (!this.nationality) { valid = false; errors.nationality = this.t('nationality_required') || 'Nationality is required.'; if (!firstErrorField) firstErrorField = 'nationality'; }
                    if (!this.month || !this.day || !this.year) { valid = false; errors.birthdate = this.t('birthdate_required') || 'Please select your birthdate.'; if (!firstErrorField) firstErrorField = 'birthdate_month'; }
                    if (!this.gender) { valid = false; errors.gender = this.t('gender_required') || 'Please select your sex.'; if (!firstErrorField) firstErrorField = 'gender'; }
                    if (!this.civil_status) { valid = false; errors.civil_status = this.t('civil_status_required') || 'Please select your civil status.'; if (!firstErrorField) firstErrorField = 'civil_status'; }
                    if (!this.place_of_birth) { valid = false; errors.place_of_birth = this.t('place_of_birth_required') || 'Place of birth is required.'; if (!firstErrorField) firstErrorField = 'place_of_birth'; }
                } else if (this.step === 2) {
                    if (!this.idType) { valid = false; errors.id_type = this.t('id_type_required') || 'Please select an ID type.'; if (!firstErrorField) firstErrorField = 'id_type'; }
                    if (!this.idScanFrontSelected) { valid = false; errors.id_scan_front = this.t('id_scan_front_required') || 'Please scan or upload the front of your government ID.'; if (!firstErrorField) firstErrorField = 'id_scan_front'; }
                    if (!this.idScanBackSelected) { valid = false; errors.id_scan_back = this.t('id_scan_back_required') || 'Please scan or upload the back of your government ID.'; if (!firstErrorField) firstErrorField = 'id_scan_back'; }
                    if (!this.id1x1Selected) { valid = false; errors.id_1x1 = this.t('id_photo_1x1_required') || 'Please upload or take a 1x1 ID picture.'; if (!firstErrorField) firstErrorField = 'id_1x1'; }
                } else if (this.step === 3) {
                    if (!this.documentTypeId) { valid = false; errors.document_type_id = this.t('select_document') || 'Please select a document.'; if (!firstErrorField) firstErrorField = 'document_type_id'; }
                    if (!this.purposeId) { valid = false; errors.purpose_id = this.t('select_purpose') || 'Please select a purpose.'; if (!firstErrorField) firstErrorField = 'purpose_id'; }
                    if (this.purposeIsOthers && !this.purposeOther) { valid = false; errors.purpose_other = this.t('purpose_other') || 'Please specify your purpose.'; if (!firstErrorField) firstErrorField = 'purpose_other'; }
                    if (!this.privacyConsent) { valid = false; errors.privacy_consent = this.t('consent_required'); if (!firstErrorField) firstErrorField = 'privacy_consent'; }
                }
                if (valid) {
                    this.goToStep(this.step + 1);
                } else {
                    this.errors = errors;
                    this.scrollToField(firstErrorField);
                }
            },

            scrollToField(fieldName) {
                this.$nextTick(() => {
                    const el = document.getElementById(fieldName);
                    if (el) {
                        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        el.focus();
                        el.classList.add('input-error');
                        setTimeout(() => el.classList.remove('input-error'), 3000);
                    }
                });
            },

            saveDraft() {
                const data = {
                    step: this.step,
                    first_name: this.first_name,
                    last_name: this.last_name,
                    middle_name: this.middle_name,
                    middleNameNone: this.middleNameNone,
                    suffix: this.suffix,
                    nationality: this.nationality,
                    occupation: this.occupation,
                    month: this.month,
                    day: this.day,
                    year: this.year,
                    gender: this.gender,
                    civil_status: this.civil_status,
                    religion: this.religion,
                    place_of_birth: this.place_of_birth,
                    personStatus: this.personStatus,
                    building_no: this.building_no,
                    unit_no: this.unit_no,
                    street: this.street,
                    road: this.road,
                    barangay: this.barangay,
                    subdivision: this.subdivision,
                    purok: this.purok,
                    contactNumber: this.contactNumber,
                    emergencyContact: this.emergencyContact,
                    email: this.email,
                    idType: this.idType,
                    idScanFrontSelected: this.idScanFrontSelected,
                    idScanBackSelected: this.idScanBackSelected,
                    id1x1Selected: this.id1x1Selected,
                    documentTypeId: this.documentTypeId,
                    purposeId: this.purposeId,
                    purposeOther: this.purposeOther,
                    privacyConsent: this.privacyConsent,
                    isPregnant: this.isPregnant,
                };
                localStorage.setItem('registration_draft', JSON.stringify(data));
            },

restoreDraft() {
                    let saved = null;
                    try {
                        saved = localStorage.getItem('registration_draft');
                    } catch (e) {
                        console.warn('register: unable to read draft from localStorage', e);
                        return;
                    }
                    if (!saved) return;

                    let data = null;
                    try {
                        data = JSON.parse(saved);
                    } catch (e) {
                        console.warn('register: corrupted draft removed from localStorage', e);
                        this.clearCorruptDraft();
                        return;
                    }
                    if (!data || typeof data !== 'object' || Array.isArray(data)) {
                        console.warn('register: invalid draft removed from localStorage');
                        this.clearCorruptDraft();
                        return;
                    }

                    const savedStep = Number(data.step);
                    this.step = (Number.isInteger(savedStep) && savedStep >= 1 && savedStep <= this.totalSteps)
                        ? savedStep
                        : 1;

                    Object.keys(data).forEach(key => {
                        if (key === 'step') return;
                        if (Object.prototype.hasOwnProperty.call(this, key) && data[key] !== null && data[key] !== undefined) {
                            const valueType = typeof data[key];
                            if (valueType === 'string' || valueType === 'number' || valueType === 'boolean') {
                                this[key] = data[key];
                            }
                        }
                    });

                    if (data.idScanFrontSelected) {
                        this.idScanFrontNeedsReupload = true;
                        this.idScanFrontSelected = false;
                    }
                    if (data.idScanBackSelected) {
                        this.idScanBackNeedsReupload = true;
                        this.idScanBackSelected = false;
                    }
                    if (data.id1x1Selected) {
                        this.id1x1NeedsReupload = true;
                        this.id1x1Selected = false;
                    }

                    this.draftRestored = true;
                    this.$nextTick(() => {
                        setTimeout(() => { this.draftRestored = false; }, 5000);
                    });
                },

                clearCorruptDraft() {
                    this.step = 1;
                    this.draftRestored = false;
                    try {
                        localStorage.removeItem('registration_draft');
                    } catch (e) {
                        console.warn('register: unable to remove corrupted draft', e);
                    }
                },

            clearForm() {
                if (this.submitting) return;
                if (confirm(this.t('clear_form_confirm') || 'Are you sure you want to clear the form?')) {
                    localStorage.removeItem('registration_draft');
                    this.first_name = '';
                    this.last_name = '';
                    this.middle_name = '';
                    this.middleNameNone = false;
                    this.suffix = '';
                    this.nationality = 'FILIPINO';
                    this.occupation = '';
                    this.month = '';
                    this.day = '';
                    this.year = '';
                    this.gender = '';
                    this.civil_status = '';
                    this.religion = '';
                    this.place_of_birth = '';
                    this.personStatus = '';
                    this.building_no = '';
                    this.unit_no = '';
                    this.street = '';
                    this.road = '';
                    this.barangay = 'MOLINO I';
                    this.subdivision = '';
                    this.purok = '';
                    this.contactNumber = '';
                    this.emergencyContact = '';
                    this.email = '';
                    this.idType = '';
                    this.idScanFrontPreview = '';
                    this.idScanFrontSource = 'upload';
                    this.idScanFrontSelected = false;
                    this.idScanFrontNeedsReupload = false;
                    this.idScanBackPreview = '';
                    this.idScanBackSource = 'upload';
                    this.idScanBackSelected = false;
                    this.idScanBackNeedsReupload = false;
                    this.id1x1Preview = '';
                    this.id1x1Source = 'upload';
                    this.id1x1Selected = false;
                    this.id1x1NeedsReupload = false;
                    this.statusPhotoPreview = '';
                    this.statusPhotoSource = 'upload';
                    this.documentTypeId = '';
                    this.purposeId = '';
                    this.purposeOther = '';
                    this.privacyConsent = false;
                    this.errors = {};
                    this.formError = '';
                    this.draftRestored = false;
                    this.step = 1;
                }
            },

            setErrors(errorBag) {
                this.errors = {};
                if (errorBag && typeof errorBag === 'object') {
                    Object.keys(errorBag).forEach(field => {
                        this.errors[field] = Array.isArray(errorBag[field]) ? errorBag[field][0] : errorBag[field];
                    });
                }
            },

                async submitForm(e) {
                // 1. Prevent default page reload if an event object is passed
                if (e && typeof e.preventDefault === 'function') {
                    e.preventDefault();
                }

                const form = document.getElementById('registration-form');
                if (!form || this.submitting || form.dataset.submitting === '1' || form.dataset.submitted === '1') return;
                this.submitting = true;
                form.dataset.submitting = '1';
                this.errors = {};
                this.formError = '';

                const formData = new FormData(form);

                try {
                    const response = await fetch('{{ route('register') }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                        body: formData,
                    });

                    // 2. Parse JSON or text safely
                    let data = null;
                    const contentType = response.headers.get('content-type') || '';
                    if (contentType.includes('application/json')) {
                        data = await response.json();
                    } else {
                        const text = await response.text();
                        try { data = JSON.parse(text); } catch (_) { data = null; }
                    }

                    // 3. Handle Validation Errors (422) — map to inline form errors
                    if (response.status === 422 && data && data.errors) {
                        this.setErrors(data.errors);
                        const firstErrorField = Object.keys(data.errors)[0] || null;
                        this.step = this.fieldStepMap[firstErrorField] || 1;
                        if (typeof this.scrollToField === 'function') {
                            this.scrollToField(firstErrorField);
                        }
                        return;
                    }

                    // 4. Handle Conflict/Duplicate Submission (409)
                    if (response.status === 409 && data && data.duplicate) {
                        localStorage.removeItem('registration_draft');
                        window.location.reload();
                        return;
                    }

                    // 5. Handle Success
                    if (response.ok && data && data.success) {
                        localStorage.removeItem('registration_draft');
                        form.dataset.submitted = '1';
                        window.location.href = data.redirect || '/dashboard';
                        return;
                    }

                    // 6. Handle Server Error (500+)
                    if (response.status >= 500) {
                        this.formError = '{{ __('The server encountered an error. Please try again later.') }}';
                        return;
                    }

                    // 7. Handle unexpected response (e.g., 302 redirect instead of JSON)
                    if (!data) {
                        console.error("Server returned HTML instead of JSON! Response status:", response.status);
                        this.formError = '{{ __('An unexpected error occurred. Please try again.') }}';
                        return;
                    }

                    // 8. Fallback for other error responses
                    this.formError = data && data.error ? data.error : '{{ __('An unexpected error occurred. Please try again.') }}';

                } catch (e) {
                    console.error("Submission exception:", e);
                    this.formError = '{{ __('A network error occurred. Please check your connection and try again.') }}';
                } finally {
                    this.submitting = false;
                    delete form.dataset.submitting;
                }
            },

            onIdTypeChange() {
                this.idScanFrontPreview = '';
                this.idScanFrontSelected = false;
                this.idScanFrontNeedsReupload = false;
                this.idScanBackPreview = '';
                this.idScanBackSelected = false;
                this.idScanBackNeedsReupload = false;
                this.errors.id_scan_front = '';
                this.errors.id_scan_back = '';
                this.saveDraft();
            },
        };
    }
</script>
@endsection
