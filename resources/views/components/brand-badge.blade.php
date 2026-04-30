<span {{ $attributes->class(['brand-badge', 'brand-badge--'.$size, 'brand-badge--placeholder' => ! $hasLogo]) }} title="{{ $label }}" aria-label="{{ $label }}">
    @if($hasLogo && $logoUrl)
        <img class="brand-badge__logo" src="{{ $logoUrl }}" alt="Logo de {{ $label }}" loading="lazy" decoding="async">
    @else
        <span class="brand-badge__fallback" aria-hidden="true">{{ $initials }}</span>
        <span class="visually-hidden">{{ $label }}</span>
    @endif
</span>
