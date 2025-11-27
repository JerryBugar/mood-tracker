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

<div class="record-item" id="mood_record_{{ $record->id }}">
    <div class="record-left">
        <img src="{{ $emoticonPath }}" alt="{{ $moodLabel }}" class="record-avatar">
        <span class="record-mood">{{ $moodLabel }}</span>
    </div>
    
    <div class="record-center">
        <span class="record-date">{{ $formattedDate }}</span>
        <span class="record-reason">{{ $record->reason ?? 'Tidak ada catatan' }}</span>
        @if($record->admin_response)
            <div class="admin-response-indicator mt-2">
                <button type="button" 
                        class="badge d-inline-flex align-items-center gap-1 border-0" 
                        style="cursor: pointer; background-color: #82272c; color: white;"
                        onclick="showAdminResponse({{ $record->id }}, {{ json_encode($record->admin_response) }}, {{ json_encode($record->admin_response_at ? \Carbon\Carbon::parse($record->admin_response_at)->locale('id_ID')->translatedFormat('l, j F Y H:i') : '') }})">
                    <i class="bi bi-check-circle-fill"></i>
                    Direspons oleh Admin/HRD
                </button>
            </div>
        @endif
    </div>
    
    <div class="record-right">
        {{ $formattedTime }}
        @if($record->admin_response)
    
        @endif
        
        @if($user && $record->user_id === $user->id)
            <div class="edit-action mt-2">
                <a href="javascript:void(0)" 
                   class="text-decoration-none" 
                   style="color: #6c757d; transition: color 0.2s;"
                   onclick="openEditMoodModal('{{ route('mood.edit', $record->id) }}')"
                   title="Edit Catatan">
                    <i class="bi bi-pencil-square" style="font-size: 1.1rem;"></i>
                </a>
            </div>
        @endif
    </div>
</div>

@if($record->admin_response)
<!-- Modal untuk menampilkan respons admin -->
<div class="modal fade" id="adminResponseModal{{ $record->id }}" tabindex="-1" aria-labelledby="adminResponseModalLabel{{ $record->id }}" aria-hidden="true" data-turbo-permanent>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #82272c;">
                <h5 class="modal-title" id="adminResponseModalLabel{{ $record->id }}">
                    <i class="bi bi-chat-dots-fill me-2"></i>Respons dari Admin/HRD
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="adminResponseContent{{ $record->id }}">{{ $record->admin_response }}</p>
                <small class="text-muted" id="adminResponseDate{{ $record->id }}">
                    Direspons pada: {{ $record->admin_response_at ? \Carbon\Carbon::parse($record->admin_response_at)->locale('id_ID')->translatedFormat('l, j F Y H:i') : '' }}
                </small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endif
