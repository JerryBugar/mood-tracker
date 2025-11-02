{{-- 
Komponen ini menampilkan tombol dengan gaya Bootstrap
Props:
- type: jenis tombol (button, submit, reset) - default: button
- variant: variasi warna tombol (primary, secondary, success, danger, warning, info, light, dark) - default: primary
- size: ukuran tombol (sm, lg) - default: tidak ada
- disabled: apakah tombol dinonaktifkan - default: false
- outline: apakah tombol hanya berupa outline - default: false
- block: apakah tombol berupa block penuh - default: false
- href: URL tujuan jika tombol berupa link - default: null
--}}
@props([
    'type' => 'button',
    'variant' => 'primary', // Contoh: primary, secondary, success, danger, warning, info, light, dark
    'size' => null, // Contoh: sm, lg
    'disabled' => false,
    'outline' => false,
    'block' => false,
    'href' => null, // Jika tombol adalah link
])

@php
    $classes = collect(['btn']);
    
    if ($outline) {
        $classes[] = "btn-outline-{$variant}";
    } else {
        $classes[] = "btn-{$variant}";
    }
    
    if ($size) {
        $classes[] = "btn-{$size}";
    }
    
    if ($block) {
        $classes[] = 'd-block w-100';
    }
    
    if ($disabled) {
        $classes[] = 'disabled';
    }
    
    $classString = $classes->implode(' ');
    
    $disabledAttr = $disabled ? 'disabled' : '';
@endphp

@if($href)
    <a href="{{ $href }}" class="{{ $classString }}" {{ $disabledAttr }} {{ $attributes }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" class="{{ $classString }}" {{ $disabledAttr }} {{ $attributes }}>
        {{ $slot }}
    </button>
@endif