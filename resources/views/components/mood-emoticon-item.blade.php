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
    // Tentukan apakah user berjenis kelamin perempuan
    $isFemale = $jenisKelamin === 'Perempuan' || $jenisKelamin === 'Cewek';
    
    $emoticonPaths = [
        'netral' => $isFemale ? asset('logo/netral1.png') : asset('logo/netral.png'),
        'senyum' => $isFemale ? asset('logo/senyum1.png') : asset('logo/senyum.png'),
        'sedih' => $isFemale ? asset('logo/sedih1.png') : asset('logo/sedih.png'),
        'lelah' => $isFemale ? asset('logo/lelah1.png') : asset('logo/lelah.png'),
        'marah' => $isFemale ? asset('logo/marah1.png') : asset('logo/marah.png'),
    ];
    $emoticonPath = $emoticonPaths[$mood] ?? $emoticonPaths['netral'];
@endphp

<div class="text-center mx-2 mx-md-5">
    <a href="javascript:void(0)" 
       class="emoticon-link" 
       onclick="loadMoodModal('{{ $mood }}')">
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