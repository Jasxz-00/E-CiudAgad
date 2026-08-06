@props([
    'type' => 'text',
    'lines' => 1,
    'class' => '',
])

@if($type === 'card')
    <div class="card !p-0 overflow-hidden {{ $class }}" {{ $attributes }}>
        <div class="skeleton h-48 w-full rounded-none"></div>
        <div class="p-4 space-y-3">
            <div class="skeleton h-4 w-3/4"></div>
            <div class="skeleton h-4 w-1/2"></div>
            <div class="skeleton h-10 w-full mt-4"></div>
        </div>
    </div>
@elseif($type === 'avatar')
    <div class="flex items-center gap-3 {{ $class }}" {{ $attributes }}>
        <div class="skeleton w-10 h-10 rounded-full"></div>
        <div class="space-y-2 flex-1">
            <div class="skeleton h-4 w-1/2"></div>
            <div class="skeleton h-3 w-1/3"></div>
        </div>
    </div>
@elseif($type === 'table-row')
    <tr {{ $attributes }}>
        @for($i = 0; $i < $lines; $i++)
            <td class="py-3 px-4"><div class="skeleton h-4 w-{{ ['3/4', '1/2', '2/3', '1/4', '3/5'][$i % 5] }}"></div></td>
        @endfor
    </tr>
@else
    <div class="space-y-2 {{ $class }}" {{ $attributes }}>
        @for($i = 0; $i < $lines; $i++)
            <div class="skeleton h-4 w-{{ ['full', '3/4', '5/6', '2/3', '1/2'][$i % 5] }}"></div>
        @endfor
    </div>
@endif
