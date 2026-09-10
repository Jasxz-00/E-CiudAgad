<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 816 1056" width="816" height="1056" style="width:100%;height:auto;">
    <rect x="18" y="18" width="780" height="1020" fill="none" stroke="#000000" stroke-width="2"/>
    <rect x="26" y="26" width="764" height="1004" fill="none" stroke="#000000" stroke-width="0.75"/>

    @if (! empty($settings['left_logo']))
        <image x="128" y="300" width="560" height="560" preserveAspectRatio="xMidYMid meet" opacity="0.12" href="{{ $settings['left_logo'] }}"/>
    @endif

    @if (! empty($settings['left_logo']))
        <image x="80" y="42" width="109" height="115" preserveAspectRatio="xMidYMid meet" href="{{ $settings['left_logo'] }}"/>
    @endif
    @if (! empty($settings['right_logo']))
        <image x="629" y="42" width="109" height="115" preserveAspectRatio="xMidYMid meet" href="{{ $settings['right_logo'] }}"/>
    @endif

    <text x="408" y="64" text-anchor="middle" font-family="'Times New Roman',Times,serif" font-size="13" letter-spacing="3" fill="#000">REPUBLIC OF THE PHILIPPINES</text>
    <text x="408" y="88" text-anchor="middle" font-family="'Times New Roman',Times,serif" font-weight="bold" font-size="14.5" fill="#000">{{ $settings['province_name'] ?? 'PROVINCE OF CAVITE' }}</text>
    <text x="408" y="110" text-anchor="middle" font-family="'Times New Roman',Times,serif" font-weight="bold" font-size="14.5" fill="#000">{{ $settings['city_name'] ?? 'CITY OF BACOOR' }}</text>
    <text x="408" y="134" text-anchor="middle" font-family="'Times New Roman',Times,serif" font-weight="bold" font-size="19" fill="#000">{{ $settings['barangay_name'] }}</text>
    <text x="408" y="156" text-anchor="middle" font-family="'Times New Roman',Times,serif" font-size="12" letter-spacing="2" fill="#000">OFFICE OF THE BARANGAY CHAIRMAN</text>
    <line x1="60" y1="170" x2="756" y2="170" stroke="#000" stroke-width="1.6"/>
    <line x1="60" y1="176" x2="756" y2="176" stroke="#000" stroke-width="0.6"/>

    <text x="235" y="918" font-family="'Courier New',monospace" font-size="11.5" fill="#000">Queue No.: {{ $queue_number ?? '—' }}</text>

    @if (! empty($show_photo))
        <rect x="668" y="190" width="72" height="72" fill="#fff" stroke="#000" stroke-width="1"/>
        @if (! empty($resident['photo']))
            <image x="672" y="194" width="64" height="64" preserveAspectRatio="xMidYMid slice" href="{{ $resident['photo'] }}"/>
        @else
            <text x="704" y="230" text-anchor="middle" font-family="'Times New Roman',Times,serif" font-size="10" fill="#888">PHOTO</text>
        @endif
        <text x="704" y="278" text-anchor="middle" font-family="'Courier New',monospace" font-size="8.5" fill="#000">1x1 ID PHOTO</text>
    @endif

    <text x="480" y="270" text-anchor="middle" font-family="'Times New Roman',Times,serif" font-weight="bold" font-size="19" letter-spacing="1" fill="#000">{{ strtoupper($document_type) }}</text>

    <rect x="60" y="190" width="150" height="648" fill="none" stroke="#000" stroke-width="0.8"/>
    <text x="136" y="210" text-anchor="middle" font-family="'Times New Roman',Times,serif" font-weight="bold" font-size="10" fill="#000">BARANGAY OFFICIALS</text>
    <line x1="60" y1="220" x2="210" y2="220" stroke="#000" stroke-width="0.5"/>
    @forelse($settings['officials'] as $official)
        <text x="70" y="{{ 242 + $loop->index * 40 }}" font-family="'Times New Roman',Times,serif" font-weight="bold" font-size="9" fill="#000">{{ mb_strtoupper($official['position_label']) }}</text>
        <text x="70" y="{{ 256 + $loop->index * 40 }}" font-family="'Times New Roman',Times,serif" font-size="10.5" fill="#000">{{ $official['name'] }}</text>
        <line x1="60" y1="{{ 270 + $loop->index * 40 }}" x2="210" y2="{{ 270 + $loop->index * 40 }}" stroke="#000" stroke-width="0.3"/>
    @empty
        <text x="70" y="242" font-family="'Times New Roman',Times,serif" font-weight="bold" font-size="9" fill="#000">PUNONG BARANGAY</text>
        <text x="70" y="256" font-family="'Times New Roman',Times,serif" font-size="10.5" fill="#000">{{ $settings['chairman_name'] ?? '____________________' }}</text>
        <line x1="60" y1="268" x2="210" y2="268" stroke="#000" stroke-width="0.3"/>
    @endforelse

    <foreignObject x="240" y="290" width="520" height="548">
        <div xmlns="http://www.w3.org/1999/xhtml" style="font-family:'Times New Roman',Times,serif;font-size:15.5px;line-height:1.55;color:#000;">
            @if (! empty($layout_body))
                {!! $layout_body !!}
            @else
                @yield('body')
            @endif
        </div>
    </foreignObject>

    <text x="235" y="838" font-family="'Courier New',monospace" font-size="11" fill="#000">CTC/Cedula No. : ____________________</text>
    <text x="235" y="858" font-family="'Courier New',monospace" font-size="11" fill="#000">Date Issued    : ____________________</text>
    <text x="235" y="878" font-family="'Courier New',monospace" font-size="11" fill="#000">Place Issued   : ____________________</text>
    <text x="235" y="898" font-family="'Courier New',monospace" font-size="11" fill="#000">OR No.         : ____________________</text>

    <line x1="240" y1="695" x2="430" y2="695" stroke="#000" stroke-width="1"/>
    <text x="329" y="710" text-anchor="middle" font-family="'Times New Roman',Times,serif" font-size="9" fill="#000">APPLICANT'S SIGNATURE</text>
    <rect x="260" y="740" width="45" height="45" fill="none" stroke="#000" stroke-width="0.8"/>
    <rect x="355" y="740" width="45" height="45" fill="none" stroke="#000" stroke-width="0.8"/>
    <text x="282" y="800" text-anchor="middle" font-family="'Courier New',monospace" font-size="10" fill="#000">L.THUMB</text>
    <text x="378" y="800" text-anchor="middle" font-family="'Courier New',monospace" font-size="10" fill="#000">R.THUMB</text>

    @php
        $chairman = collect($settings['officials'])->firstWhere('position', 'punong_barangay');
        $secretary = collect($settings['officials'])->firstWhere('position', 'secretary');
    @endphp
    @if (! empty($settings['signature']))
        <image x="565" y="622" width="180" height="60" preserveAspectRatio="xMidYMid meet" href="{{ $settings['signature'] }}"/>
    @else
        <text x="655" y="680" text-anchor="middle" font-family="'Times New Roman',Times,serif" font-size="10" font-style="italic" fill="#000">(Sgd.)</text>
    @endif
    <line x1="550" y1="695" x2="755" y2="695" stroke="#000" stroke-width="1"/>
    <text x="655" y="715" text-anchor="middle" font-family="'Times New Roman',Times,serif" font-weight="bold" font-size="14" fill="#000">{{ $chairman['name'] ?? $settings['chairman_name'] ?? '________________________' }}</text>
    <text x="655" y="730" text-anchor="middle" font-family="'Times New Roman',Times,serif" font-size="11.5" fill="#000">Punong Barangay</text>

    <text x="655" y="775" text-anchor="middle" font-family="'Times New Roman',Times,serif" font-size="10" font-style="italic" fill="#000">(Sgd.)</text>
    <line x1="550" y1="790" x2="755" y2="790" stroke="#000" stroke-width="1"/>
    <text x="655" y="810" text-anchor="middle" font-family="'Times New Roman',Times,serif" font-weight="bold" font-size="13" fill="#000">{{ $secretary['name'] ?? '________________________' }}</text>
    <text x="655" y="825" text-anchor="middle" font-family="'Times New Roman',Times,serif" font-size="11.5" fill="#000">Barangay Secretary</text>

    <text x="408" y="1010" text-anchor="middle" font-family="'Times New Roman',Times,serif" font-weight="bold" font-size="11.5" letter-spacing="1" fill="#000">NOT VALID WITHOUT OFFICIAL DRY SEAL</text>
</svg>