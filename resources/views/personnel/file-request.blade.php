@extends('layouts.app')

@section('title', 'File Request for Resident')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-6">File Document Request for Resident</h1>

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-600/15 text-green-600 dark:text-green-400 border border-success/30 rounded-xl text-sm">{{ session('success') }}</div>
    @endif

    <div class="card">
        <script>
            window.__residents = {{ Illuminate\Support\Js::from($residents->map(fn ($r) => [
                'id' => $r->id,
                'full_name' => $r->full_name,
                'contact_number' => $r->contact_number,
                'barangay' => $r->barangay,
                'purok' => $r->purok,
                'category' => $r->category,
            ])->values()) }};
        </script>

        <form method="POST" action="{{ route('personnel.resident-requests.store') }}" class="space-y-5">
            @csrf

            <div x-data="residentPicker(window.__residents, '{{ old('resident_id') }}')">
                <label class="label">Resident <span class="text-red-600 dark:text-red-400">*</span></label>
                <input type="hidden" name="resident_id" x-bind:value="selectedId">

                <input type="search" x-model="query" placeholder="Search by name or contact number..." class="input-field mb-2" autocomplete="off">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-3">
                    <select x-model="filterBarangay" class="select-field text-sm">
                        <option value="">All Barangays</option>
                        <template x-for="b in barangays" :key="b">
                            <option :value="b" x-text="b"></option>
                        </template>
                    </select>
                    <select x-model="filterCategory" class="select-field text-sm">
                        <option value="">All Categories</option>
                        <option value="regular">Regular</option>
                        <option value="senior">Senior Citizen</option>
                        <option value="pwd">PWD</option>
                        <option value="pregnant">Pregnant</option>
                    </select>
                </div>

                <div class="max-h-64 overflow-y-auto border border-gray-200 dark:border-gray-700 rounded-xl divide-y divide-gray-200 dark:divide-gray-700">
                    <template x-if="filteredResidents.length === 0">
                        <p class="p-4 text-sm text-gray-600 dark:text-gray-400">No residents match your search.</p>
                    </template>
                    <template x-for="r in filteredResidents" :key="r.id">
                        <label class="flex items-start gap-3 p-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                               :class="selectedId == r.id ? 'bg-primary-50 dark:bg-primary-900/20' : ''">
                            <input type="radio" name="resident_pick" :value="r.id" x-model="selectedId" class="mt-1 w-4 h-4 text-primary-700 border-gray-300 dark:border-gray-600">
                            <span class="text-sm">
                                <span class="font-medium" x-text="r.full_name"></span>
                                <span class="text-xs text-gray-600 dark:text-gray-400 block" x-text="(r.barangay || '') + (r.purok ? ', ' + r.purok : '') + (r.contact_number ? ' - ' + r.contact_number : '')"></span>
                            </span>
                        </label>
                    </template>
                </div>
                @error('resident_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

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
                </select>
                @error('purpose_id') <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div id="other-purpose-container" style="display: none;">
                <label for="purpose_other" class="label">Specify Purpose</label>
                <input id="purpose_other" type="text" name="purpose_other" value="{{ old('purpose_other') }}" class="input-field uppercase-input" placeholder="Please specify your purpose" autocomplete="off">
            </div>

            <script>
                document.querySelector('select[name="purpose_id"]')?.addEventListener('change', function() {
                    const container = document.getElementById('other-purpose-container');
                    container.style.display = this.options[this.selectedIndex]?.text === 'Others' ? 'block' : 'none';
                });
            </script>

            <div class="pt-4 flex flex-wrap gap-3">
                <button type="submit" class="btn-primary">Submit Request</button>
                <a href="{{ route('personnel.dashboard') }}" class="btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
    function residentPicker(residents, initialId) {
        return {
            residents: residents || [],
            query: '',
            filterBarangay: '',
            filterCategory: '',
            selectedId: initialId,

            get barangays() {
                return [...new Set(this.residents.map(r => r.barangay).filter(Boolean))].sort();
            },

            get filteredResidents() {
                const q = this.query.trim().toLowerCase();
                return this.residents.filter(r => {
                    if (this.filterBarangay && r.barangay !== this.filterBarangay) return false;
                    if (this.filterCategory && r.category !== this.filterCategory) return false;
                    if (q) {
                        const haystack = ((r.full_name || '') + ' ' + (r.contact_number || '')).toLowerCase();
                        if (!haystack.includes(q)) return false;
                    }
                    return true;
                });
            },
        };
    }
</script>
@endsection
