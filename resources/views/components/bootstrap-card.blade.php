{{-- 
Komponen ini menampilkan card dengan gaya Bootstrap
Props:
- type: jenis card (default, primary, success, info, warning, danger) - default: default
- outline: apakah card hanya berupa border - default: false
- header: teks header card - default: null
- footer: teks footer card - default: null
--}}
@props([
    'type' => 'default', // Contoh: default, primary, success, info, warning, danger
    'outline' => false,
    'header' => null,
    'footer' => null,
])

@php
    $classes = collect(['card']);
    
    if ($outline) {
        $classes[] = "border-{$type}";
    } else {
        $classes[] = "bg-{$type}";
    }
    
    $classString = $classes->implode(' ');
@endphp

<div class="{{ $classString }}" {{ $attributes }}>
    @if($header)
        <div class="card-header">{{ $header }}</div>
    @endif
    
    <div class="card-body">
        {{ $slot }}
    </div>
    
    @if($footer)
        <div class="card-footer">{{ $footer }}</div>
    @endif
</div>