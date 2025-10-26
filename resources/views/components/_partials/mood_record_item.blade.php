<div class="record-item">
    <div class="record-left">
        @if(isset($user) && $user->avatar)
            <img src="{{ $user->avatar }}" alt="Avatar" class="record-avatar">
        @endif
        <span class="record-mood">{{ ucfirst($moodLabels[$record->mood] ?? $record->mood) }}</span>
    </div>
    
    <div class="record-center">
        <span class="record-date">{{ \Carbon\Carbon::parse($record->created_at)->locale('id_ID')->translatedFormat('l, j F Y') }}</span>
        <span class="record-reason">{{ $record->reason ?? 'Tidak ada catatan' }}</span>
    </div>
    
    <div class="record-right">
        {{ \Carbon\Carbon::parse($record->created_at)->format('g:i A') }}
    </div>
</div>