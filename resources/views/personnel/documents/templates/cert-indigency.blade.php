@extends('personnel.documents.partials.letterhead')

@section('validity')Six (6) Months @endsection

@section('body')
    <p style="text-align:center;margin:34px 0 20px;"><b>TO WHOM IT MAY CONCERN</b></p>

    <p style="margin-bottom:16px;text-indent:36px;">
        This is to certify that <b>{{ $resident['full_name_natural'] }}</b>, {{ $resident['age'] }} years old,
        {{ $resident['civil_status'] }}, Filipino citizen, and a resident of
        <b>{{ $resident['address'] }}</b>, belongs to an <b>indigent family</b> and has no
        sufficient means of income or financial capacity to cover the expenses for the purpose stated herein.
    </p>

    <p style="margin-bottom:16px;text-indent:36px;">
        This certification is issued upon the request of the above-named person for
        <b>{{ $purpose }}</b> purposes.
    </p>

    <p style="text-indent:36px;">
        Issued this <b>{{ \Carbon\Carbon::parse($issued_on)->format('jS') }} day of
        {{ \Carbon\Carbon::parse($issued_on)->format('F, Y') }}</b> at {{ $settings['barangay_name'] }} in support of the above-mentioned purpose.
    </p>
@endsection