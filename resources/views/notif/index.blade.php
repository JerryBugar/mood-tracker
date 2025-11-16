@extends('layouts.internal')

@push('styles')
@vite(['resources/css/notif/notif.css'])
@endpush

@section('main-content')
    <div class="container-fluid">
        <h1 style="color: #82272c; margin-top: 20px; text-align: center;">
            <i class="bi bi-bell-fill" style="color: #82272c; margin-right: 10px; vertical-align: middle;"></i>
            Notification
        </h1>

        <!-- Push Notification Settings -->
        <div id="push-notification-settings" class="push-notification-settings" data-turbo-permanent style="max-width: 600px; margin: 20px auto; padding: 15px; background: #f8f9fa; border-radius: 8px; border: 1px solid #dee2e6;">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div style="flex: 1;">
                    <label for="push-notification-toggle" style="font-weight: 600; color: #82272c; margin-bottom: 5px; display: block;">
                        <i class="bi bi-bell-slash-fill" style="margin-right: 8px;"></i>
                        Push Notification
                    </label>
                    <p id="push-notification-status" style="margin: 0; color: #6c757d; font-size: 0.9em;">
                        Memuat status...
                    </p>
                </div>
                <div class="form-check form-switch" style="margin-left: 15px;">
                    <input class="form-check-input" type="checkbox" id="push-notification-toggle" style="width: 3rem; height: 1.5rem; cursor: pointer;">
                </div>
            </div>
            <p style="margin-top: 10px; margin-bottom: 0; font-size: 0.85em; color: #6c757d;">
                <i class="bi bi-info-circle"></i> Aktifkan untuk menerima notifikasi langsung di browser/device Anda
            </p>
        </div>

        <turbo-frame id="notifications_frame" data-turbo-action="replace" data-turbo-temporary>
            @include('notif._partials.notifications_list', ['notifications' => $notifications])
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
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true" data-turbo-permanent>
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
