<div class="notifications-container {{ $notifications->count() == 0 ? 'empty-state' : '' }}">
    @if($notifications->count() > 0)
        @php
            $unreadCount = 0;
            $totalNotifications = $notifications->count();
            foreach ($notifications as $notification) {
                if (!($notification->pivot->is_read ?? false)) {
                    $unreadCount++;
                }
            }
        @endphp
        @if($unreadCount > 1)
            <div class="mark-all-container">
                <form action="{{ url(route('notif.read-all')) }}" method="POST" data-turbo-frame="notifications_frame" data-turbo-stream>
                    @csrf
                    <button type="submit" class="btn-mark-all-read">
                        <i class="bi bi-check-all"></i> Tandai Semua Sudah Dibaca
                    </button>
                </form>
            </div>
        @endif
        @if($unreadCount == 0 && $totalNotifications > 0)
            <div class="mark-all-container">
                <button type="button" class="btn-delete-all" data-action="delete-all" data-turbo-frame="notifications_frame">
                    <i class="bi bi-trash-fill"></i> Hapus Semua Notif
                </button>
            </div>
        @endif
        <div class="notification-list">
            @foreach($notifications as $notification)
                @php
                    $isRead = $notification->pivot->is_read ?? false;
                @endphp
                <div class="notification-item {{ $isRead ? 'read' : 'unread' }}" 
                     data-notification-id="{{ $notification->id }}">
                    <div class="notification-header">
                        <h4 class="notification-title">
                            @if($notification->type === 'individual')
                                <i class="bi bi-person-fill" style="color: #82242d;"></i> Notifikasi Individu
                            @elseif($notification->type === 'group')
                                <i class="bi bi-people-fill" style="color: #82242d;"></i> Notifikasi Divisi: {{ $notification->division }}
                            @else
                                <i class="bi bi-broadcast" style="color: #82242d;"></i> Notifikasi Semua Karyawan
                            @endif
                        </h4>
                        <span class="notification-date">
                            {{ \Carbon\Carbon::parse($notification->created_at)->locale('id_ID')->translatedFormat('l, j F Y H:i') }}
                        </span>
                    </div>
                    <div class="notification-message">
                        {{ $notification->message }}
                    </div>
                    @if(!$isRead)
                        <form action="{{ url(route('notif.read', $notification->id)) }}" method="POST" data-turbo-frame="notifications_frame" data-turbo-stream style="display: inline;">
                            @csrf
                            <button type="submit" class="btn-mark-read">
                                Tandai Sudah Dibaca
                            </button>
                        </form>
                    @else
                        <span class="read-badge">
                            <i class="bi bi-check-circle-fill"></i> Sudah Dibaca
                        </span>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="no-notifications">
            <div class="no-notifications-icon">
                <i class="bi bi-bell-slash"></i>
            </div>
            <h3 class="no-notifications-title">Belum ada notifikasi</h3>
            <p class="no-notifications-subtitle">Notifikasi dari Admin/HRD akan muncul di sini</p>
        </div>
    @endif
</div>

