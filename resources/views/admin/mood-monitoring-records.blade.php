<div class="mood-table-container">
    <table class="mood-table">
        <thead>
            <tr>
                <th>Karyawan</th>
                <th>Divisi</th>
                <th>Tanggal</th>
                <th>Mood</th>
                <th>Alasan</th>
                <th>Tindakan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($moodRecords as $record)
            <tr>
                <td>
                    <div class="employee-info">
                        @if($record->user->avatar)
                            <img src="{{ $record->user->avatar }}" alt="Avatar" class="employee-avatar">
                        @else
                            <div class="employee-avatar bg-light d-flex align-items-center justify-content-center">
                                {{ strtoupper(substr($record->user->name, 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <div>{{ $record->user->name }}</div>
                            <div class="text-muted small">{{ $record->user->email }}</div>
                        </div>
                    </div>
                </td>
                <td>{{ $record->user->division ?: 'Tidak ada divisi' }}</td>
                <td>{{ $record->created_at->format('d M Y') }}</td>
                <td>
                    @php
                        $moodClass = '';
                        $moodLabel = '';
                        switch($record->mood) {
                            case 'senyum':
                                $moodClass = 'mood-happy';
                                $moodLabel = 'Senang';
                                break;
                            case 'sedih':
                                $moodClass = 'mood-sad';
                                $moodLabel = 'Sedih';
                                break;
                            case 'lelah':
                                $moodClass = 'mood-tired';
                                $moodLabel = 'Lelah';
                                break;
                            case 'marah':
                                $moodClass = 'mood-angry';
                                $moodLabel = 'Marah';
                                break;
                            case 'netral':
                                $moodClass = 'mood-neutral';
                                $moodLabel = 'Biasa Saja';
                                break;
                            default:
                                $moodClass = 'mood-neutral';
                                $moodLabel = $record->mood;
                        }
                    @endphp
                    <span><div class="mood-indicator {{ $moodClass }}"></div> {{ $moodLabel }}</span>
                </td>
                <td>{{ $record->reason ?: '-' }}</td>
                <td>{{ $record->action_suggestion ?: '-' }}</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-notification" onclick="sendNotification({{ $record->user->id }})">Notifikasi</button>
                        <button class="btn-schedule" onclick="scheduleTask({{ $record->user->id }})">Jadwal</button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada data mood hari ini</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="pagination">
    {{ $moodRecords->links() }}
</div>