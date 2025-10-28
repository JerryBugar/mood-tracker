<div class="day-view-header">
    <h4>{{ $formattedDate }}</h4>
    <button type="button" class="btn-close" onclick="hideDayView()" aria-label="Close"></button>
</div>

@if($records->count() > 0)
    <div class="day-records-list">
        @foreach($records as $record)
            <div class="record-item">
                <div class="record-left">
                    <div class="mood-indicator {{ $record->mood }}"></div>
                    <div class="record-mood">{{ ['netral' => 'Biasa saja', 'senyum' => 'Senang', 'sedih' => 'Sedih', 'lelah' => 'Lelah', 'marah' => 'Marah'][$record->mood] ?? $record->mood }}</div>
                </div>
                
                <div class="record-center">
                    <div class="record-reason">
                        {{ $record->reason ?? 'Tidak ada catatan' }}
                    </div>
                    
                    @if($record->suggestion_action)
                        <div class="record-suggestion">
                            <strong>Saran:</strong> {{ $record->suggestion_action }}
                        </div>
                    @endif
                </div>
                
                <div class="record-time">
                    {{ $record->created_at->format('H:i') }}
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="no-records-message">
        <p>Tidak ada catatan mood untuk hari ini.</p>
        <p>Ayo ceritakan perasaanmu dengan memilih emoticon di halaman utama!</p>
    </div>
@endif

<script>
    function hideDayView() {
        document.getElementById('calendar-day-view').style.display = 'none';
    }
</script>