<div class="record-item">
    <div class="record-left">
        @if(isset($user))
            @php
                $jenisKelamin = $user->jenis_kelamin ?? '';
                $emoticonPaths = [
                    'netral' => $jenisKelamin === 'Cewek' ? asset('logo/netral1.png') : asset('logo/netral.png'),
                    'senyum' => $jenisKelamin === 'Cewek' ? asset('logo/senyum1.png') : asset('logo/senyum.png'),
                    'sedih' => $jenisKelamin === 'Cewek' ? asset('logo/sedih1.png') : asset('logo/sedih.png'),
                    'lelah' => $jenisKelamin === 'Cewek' ? asset('logo/lelah1.png') : asset('logo/lelah.png'),
                    'marah' => $jenisKelamin === 'Cewek' ? asset('logo/marah1.png') : asset('logo/marah.png'),
                ];
                $emoticonPath = $emoticonPaths[$record->mood] ?? $emoticonPaths['netral'];
            @endphp
            <img src="{{ $emoticonPath }}" alt="{{ ucfirst($moodLabels[$record->mood] ?? $record->mood) }}" class="record-avatar">
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