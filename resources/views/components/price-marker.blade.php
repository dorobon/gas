@props([
    'value' => null,
    'compact' => false,
    'unit' => '€/l',
])

@php
    $hasValue = $value !== null && $value !== '';
    $formatted = $hasValue ? number_format((float) $value, 3, ',', '.') : null;
    $characters = $formatted ? str_split($formatted) : [];
@endphp

@if($hasValue)
    <span
        {{ $attributes->class(['price-marker', 'price-marker--compact' => $compact]) }}
        aria-label="{{ $formatted.' '.$unit }}"
    >
        <span class="price-marker__digits" aria-hidden="true">
            @foreach($characters as $index => $character)
                @if(in_array($character, [',', '.'], true))
                    <span class="price-marker__separator">{{ $character }}</span>
                @else
                    <span class="price-marker__digit" style="--digit-index: {{ $index }};">{{ $character }}</span>
                @endif
            @endforeach
        </span>
        <span class="price-marker__unit">{{ $unit }}</span>
    </span>
@else
    <span {{ $attributes->class(['price-marker', 'price-marker--compact' => $compact, 'price-marker--empty']) }}>
        <span class="price-marker__empty">Sin dato</span>
    </span>
@endif