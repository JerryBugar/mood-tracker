{{-- 
Komponen ini menampilkan satu item catatan mood
Props:
- record: objek MoodRecord yang akan ditampilkan
- user: objek User untuk menentukan jenis kelamin dan avatar
--}}
@props([
    'record' => null,
    'user' => null
])

@php
    if (!$record) {
        throw new InvalidArgumentException('Record is required for mood-record-item component');
    }
    
    // Use accessor from model if available
    $moodLabel = $record->mood_label ?? ucfirst($record->mood);
    $formattedDate = $record->formatted_date ?? \Carbon\Carbon::parse($record->created_at)->locale('id_ID')->translatedFormat('l, j F Y');
    $formattedTime = $record->formatted_time ?? \Carbon\Carbon::parse($record->created_at)->format('g:i A');
    
    $jenisKelamin = $user ? $user->jenis_kelamin : '';
    // Tentukan apakah user berjenis kelamin perempuan
    $isFemale = $jenisKelamin === 'Perempuan' || $jenisKelamin === 'Cewek';
    
    $emoticonPaths = [
        'netral' => $isFemale ? asset('logo/netral1.png') : asset('logo/netral.png'),
        'senyum' => $isFemale ? asset('logo/senyum1.png') : asset('logo/senyum.png'),
        'sedih' => $isFemale ? asset('logo/sedih1.png') : asset('logo/sedih.png'),
        'lelah' => $isFemale ? asset('logo/lelah1.png') : asset('logo/lelah.png'),
        'marah' => $isFemale ? asset('logo/marah1.png') : asset('logo/marah.png'),
    ];
    $emoticonPath = $emoticonPaths[$record->mood] ?? $emoticonPaths['netral'];
@endphp

<div class="record-item">
    <div class="record-left">
        <img src="{{ $emoticonPath }}" alt="{{ $moodLabel }}" class="record-avatar">
        <span class="record-mood">{{ $moodLabel }}</span>
    </div>
    
    <div class="record-center">
        <span class="record-date">{{ $formattedDate }}</span>
        <span class="record-reason">{{ $record->reason ?? 'Tidak ada catatan' }}</span>
    </div>
    
    <div class="record-right">
        {{ $formattedTime }}
    </div>
</div>