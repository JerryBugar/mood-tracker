{{-- 
Komponen ini menampilkan satu item emoticon mood
Props:
- mood: jenis mood (senyum, sedih, netral, lelah, marah)
- title: judul untuk alt text dan title
- jenisKelamin: jenis kelamin user untuk menentukan avatar
--}}
@props([
    'mood' => '',
    'title' => '',
    'jenisKelamin' => '',
])

@php
    $emoticonPaths = [
        'netral' => $jenisKelamin === 'Cewek' ? asset('logo/netral1.png') : asset('logo/netral.png'),
        'senyum' => $jenisKelamin === 'Cewek' ? asset('logo/senyum1.png') : asset('logo/senyum.png'),
        'sedih' => $jenisKelamin === 'Cewek' ? asset('logo/sedih1.png') : asset('logo/sedih.png'),
        'lelah' => $jenisKelamin === 'Cewek' ? asset('logo/lelah1.png') : asset('logo/lelah.png'),
        'marah' => $jenisKelamin === 'Cewek' ? asset('logo/marah1.png') : asset('logo/marah.png'),
    ];
    $emoticonPath = $emoticonPaths[$mood] ?? $emoticonPaths['netral'];
@endphp

<div class="text-center mx-2 mx-md-5">
    <a href="{{ route('mood.modal', ['mood' => $mood]) }}" data-turbo-frame="mood_modal_content" class="emoticon-link">
        <div class="emoticon-background">
            <img 
                src="{{ $emoticonPath }}" 
                alt="{{ $title }}" 
                class="mood-emoticon" 
                data-mood="{{ $mood }}" 
                style="width: 50px; height: 50px; display: block;"
                srcset="{{ $emoticonPath }} 1x, {{ $emoticonPath }} 2x"
            >
        </div>
    </a>
</div>