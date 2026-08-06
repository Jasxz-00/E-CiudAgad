@props([
    'headers' => [],
    'striped' => true,
    'class' => '',
])

<div class="table-container {{ $class }}" {{ $attributes }}>
    <table class="w-full">
        @if(count($headers))
            <thead class="table-header">
                <tr>
                    @foreach($headers as $header)
                        <th scope="col">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody class="table-body">
            {{ $slot }}
        </tbody>
    </table>
</div>
