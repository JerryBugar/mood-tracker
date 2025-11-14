@extends('layouts.internal')

@section('main-content')
    <div class="container-fluid">
        <h1 style="color: #82272c; margin-top: 20px; text-align: center;">
            <i class="bi bi-bell-fill" style="color: #82272c; margin-right: 10px; vertical-align: middle;"></i>
            Notifikasi
        </h1>

        <div class="notifications-container {{ $notifications->count() == 0 ? 'empty-state' : '' }}">
            @if($notifications->count() > 0)
                @php
                    $totalNotifications = $notifications->count();
                    $hasUnread = false;
                    foreach ($notifications as $notification) {
                        if (!($notification->pivot->is_read ?? false)) {
                            $hasUnread = true;
                            break;
                        }
                    }
                @endphp
                @if($totalNotifications > 1 && $hasUnread)
                    <div class="mark-all-container">
                        <button class="btn-mark-all-read" onclick="markAllAsRead()">
                            <i class="bi bi-check-all"></i> Tandai Semua Sudah Dibaca
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
                                <button class="btn-mark-read" onclick="markAsRead({{ $notification->id }})">
                                    Tandai Sudah Dibaca
                                </button>
                            @else
                                <span class="read-badge">
                                    <i class="bi bi-check-circle-fill"></i> Sudah Dibaca
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="pagination-container" style="margin-top: 30px; display: flex; justify-content: center;">
                    {{ $notifications->links() }}
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
    </div>

    <style>
        .notifications-container {
            margin-top: 30px;
        }

        .notifications-container.empty-state {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 250px);
        }

        .notifications-container > .notification-list {
            width: 100%;
        }

        .mark-all-container {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
        }

        .btn-mark-all-read {
            background-color: #82242d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 0.95rem;
            cursor: pointer;
            transition: background-color 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-mark-all-read:hover {
            background-color: #6a1d24;
        }

        .btn-mark-all-read:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .notification-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .notification-item {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #82242d;
            transition: all 0.3s ease;
        }

        .notification-item.unread {
            background: #fff5f5;
            border-left-width: 6px;
        }

        .notification-item:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .notification-title {
            color: #82242d;
            font-size: 1rem;
            font-weight: 600;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .notification-date {
            color: #6c757d;
            font-size: 0.85rem;
        }

        .notification-message {
            color: #333;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .btn-mark-read {
            background-color: #82242d;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .btn-mark-read:hover {
            background-color: #6a1d24;
        }

        .read-badge {
            color: #28a745;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .no-notifications {
            text-align: center;
            padding: 60px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
        }

        .no-notifications-icon {
            width: 80px;
            height: 80px;
            background-color: #f8f9fa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
        }

        .no-notifications-icon i {
            font-size: 48px;
            color: #d0d0d0;
        }

        .no-notifications-title {
            color: #82272c;
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0 0 12px 0;
        }

        .no-notifications-subtitle {
            color: #6c757d;
            font-size: 0.95rem;
            margin: 0;
            max-width: 400px;
            line-height: 1.5;
        }

        @media (max-width: 767px) {
            .notification-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .notification-date {
                font-size: 0.8rem;
            }

            .no-notifications {
                padding: 40px 20px;
                min-height: 300px;
            }

            .no-notifications-icon {
                width: 60px;
                height: 60px;
                margin-bottom: 20px;
            }

            .no-notifications-icon i {
                font-size: 36px;
            }

            .no-notifications-title {
                font-size: 1.1rem;
            }

            .no-notifications-subtitle {
                font-size: 0.9rem;
            }
        }
    </style>

    <script>
        function markAsRead(notificationId) {
            fetch(`/notif/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI
                    const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
                    if (notificationItem) {
                        notificationItem.classList.remove('unread');
                        notificationItem.classList.add('read');
                        notificationItem.style.background = 'white';
                        notificationItem.style.borderLeftWidth = '4px';
                        
                        // Ganti button dengan badge
                        const button = notificationItem.querySelector('.btn-mark-read');
                        if (button) {
                            button.outerHTML = '<span class="read-badge"><i class="bi bi-check-circle-fill"></i> Sudah Dibaca</span>';
                        }
                    }
                    
                    // Cek apakah masih ada unread, jika tidak sembunyikan tombol mark all
                    checkUnreadCount();
                } else {
                    alert('Gagal menandai notifikasi sebagai sudah dibaca');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan');
            });
        }

        function markAllAsRead() {
            const button = document.querySelector('.btn-mark-all-read');
            if (button) {
                button.disabled = true;
                button.innerHTML = '<i class="bi bi-hourglass-split"></i> Memproses...';
            }

            fetch('/notif/read-all', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update semua notifikasi yang belum dibaca
                    const unreadItems = document.querySelectorAll('.notification-item.unread');
                    unreadItems.forEach(item => {
                        item.classList.remove('unread');
                        item.classList.add('read');
                        item.style.background = 'white';
                        item.style.borderLeftWidth = '4px';
                        
                        // Ganti button dengan badge
                        const button = item.querySelector('.btn-mark-read');
                        if (button) {
                            button.outerHTML = '<span class="read-badge"><i class="bi bi-check-circle-fill"></i> Sudah Dibaca</span>';
                        }
                    });
                    
                    // Sembunyikan tombol mark all
                    const markAllContainer = document.querySelector('.mark-all-container');
                    if (markAllContainer) {
                        markAllContainer.style.display = 'none';
                    }
                } else {
                    alert('Gagal menandai semua notifikasi sebagai sudah dibaca');
                    if (button) {
                        button.disabled = false;
                        button.innerHTML = '<i class="bi bi-check-all"></i> Tandai Semua Sudah Dibaca';
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan');
                if (button) {
                    button.disabled = false;
                    button.innerHTML = '<i class="bi bi-check-all"></i> Tandai Semua Sudah Dibaca';
                }
            });
        }

        function checkUnreadCount() {
            const unreadItems = document.querySelectorAll('.notification-item.unread');
            const allItems = document.querySelectorAll('.notification-item');
            const markAllContainer = document.querySelector('.mark-all-container');
            
            // Sembunyikan tombol jika tidak ada unread atau jumlah notifikasi <= 1
            if ((unreadItems.length === 0 || allItems.length <= 1) && markAllContainer) {
                markAllContainer.style.display = 'none';
            }
        }
    </script>
@endsection
