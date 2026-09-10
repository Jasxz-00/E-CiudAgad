@extends('personnel.documents.partials.letterhead')

@section('validity')Three (3) Months @endsection

@section('body')
    <p style="text-align:center;margin:34px 0 20px;"><b>TO WHOM IT MAY CONCERN</b></p>

    <p style="margin-bottom:16px;text-indent:36px;">
        This is to certify that <b>{{ $resident['full_name_natural'] }}</b>, {{ $resident['age'] }} years old,
        {{ $resident['civil_status'] }}, Filipino citizen, is a <b>bona fide resident</b> of
        <b>{{ $resident['address'] }}</b> in this barangay, and that he/she has been residing therein for a considerable length of time.
    </p>

    <p style="margin-bottom:16px;text-indent:36px;">
        This certification is issued upon the request of the above-named person for
        <b>{{ $purpose }}</b> purposes based on the records of this Barangay.
    </p>

    <p style="text-indent:36px;">
        Issued this <b>{{ \Carbon\Carbon::parse($issued_on)->format('jS') }} day of
        {{ \Carbon\Carbon::parse($issued_on)->format('F, Y') }}</b> at {{ $settings['barangay_name'] }} in support of the above-mentioned purpose.
    </p>
@endsection