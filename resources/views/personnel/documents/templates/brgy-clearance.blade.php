@extends('personnel.documents.partials.letterhead')

@section('validity')Six (6) Months @endsection

@section('body')
    <p style="text-align:center;margin:34px 0 20px;"><b>TO WHOM IT MAY CONCERN</b></p>

    <p style="margin-bottom:16px;text-indent:36px;">
        This is to certify that <b>{{ $resident['full_name_natural'] }}</b>, {{ $resident['age'] }} years old,
        {{ $resident['civil_status'] }}, a Filipino citizen, and a bona fide resident of
        <b>{{ $resident['address'] }}</b>, is known to me to be of good moral character and a law-abiding citizen of this barangay.
    </p>

    <p style="margin-bottom:16px;text-indent:36px;">
        This certification is issued upon the request of the above-named person for
        <b>{{ $purpose }}</b> purposes. No derogatory or criminal record has ever been
        recorded against the said person in this barangay.
    </p>

    <p style="text-indent:36px;">
        This clearance is issued this <b>{{ \Carbon\Carbon::parse($issued_on)->format('jS') }} day of
        {{ \Carbon\Carbon::parse($issued_on)->format('F, Y') }}</b> in support of the above-mentioned purpose and shall be
        valid for six (6) months from the date of issue.
    </p>

    @if ($purpose_code === 'BAIL_LEGAL')
        <div style="border:2px solid #000;padding:8px 12px;margin-top:22px;font-size:14px;">
            <b>NOTE:</b> This clearance is issued for bail purposes and requires the <b>original signature</b> of the Punong Barangay.
            Photocopies, scanned reproductions, or e-copies of this document shall not be honored.
        </div>
    @endif
@endsection