@extends('layouts.internal')

@push('styles')
@vite(['resources/css/notif/notif.css'])
@endpush

@section('main-content')
    <div class="container-fluid">
        <h1 style="color: #82272c; margin-top: 20px; text-align: center;">
            <i class="bi bi-bell-fill" style="color: #82272c; margin-right: 10px; vertical-align: middle;"></i>
            Notifikasi
        </h1>

        <turbo-frame id="notifications_frame" data-turbo-action="replace">
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
                        <button class="btn-mark-all-read" onclick="markAllAsRead()">
                            <i class="bi bi-check-all"></i> Tandai Semua Sudah Dibaca
                        </button>
                    </div>
                @endif
                @if($unreadCount == 0 && $totalNotifications > 0)
                    <div class="mark-all-container">
                        <button class="btn-delete-all" onclick="deleteAllNotifications()">
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
        </turbo-frame>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 99999;">
        <div id="notificationToast" class="toast custom-toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-content-wrapper">
                <div class="toast-icon-wrapper">
                    <i class="toast-icon bi bi-check-circle-fill"></i>
                </div>
                <div class="toast-body-content">
                    <div class="toast-title" id="toast-title">Berhasil</div>
                    <div class="toast-message" id="toast-message">Notifikasi berhasil!</div>
                </div>
                <button type="button" class="toast-close-btn" data-bs-dismiss="toast" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmModalLabel">Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Apakah Anda yakin ingin menghapus semua notifikasi yang sudah dibaca?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn" data-bs-dismiss="modal">Hapus</button>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
@vite(['resources/js/notif/notif.js'])
@endpush
@endsection
