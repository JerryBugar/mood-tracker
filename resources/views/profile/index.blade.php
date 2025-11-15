@extends('layouts.internal')

@push('styles')
@vite(['resources/css/profile/profile.css'])
@endpush

@section('main-content')

<div class="container-fluid">

    <div class="profile-card">
        <div id="profile-content">
            @include('profile._partials.profile_content')
        </div>

        <div class="button-container">
            <button class="btn-edit" onclick="showEditProfile()">
                <i class="bi bi-pencil-square"></i>
                Edit Profil
            </button>
            
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
            
            <button class="btn-logout" onclick="confirmLogout()">
                <i class="bi bi-box-arrow-right"></i>
                Keluar
            </button>
        </div>
    </div>
</div>



<!-- Modal Edit Profil (dengan data-turbo-permanent untuk mencegah perubahan oleh Turbo) -->
<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true" data-turbo-permanent>
    <div id="edit-profile-modal-container" class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">Edit Profil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="edit-profile-form-container">
                @include('profile._partials.profile_form')
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Logout -->
<div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmModalLabel" aria-hidden="true" data-turbo-permanent>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logoutConfirmModalLabel">Konfirmasi Keluar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeLogoutModalBtn"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Apakah Anda yakin ingin keluar?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelLogoutBtn">Tidak</button>
                <button type="button" class="btn btn-danger" id="confirmLogoutBtn">Ya, Keluar</button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 99999;">
    <div id="successToast" class="toast custom-toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-content-wrapper">
            <div class="toast-icon-wrapper">
                <i class="toast-icon bi bi-check-circle-fill"></i>
            </div>
            <div class="toast-body-content">
                <div class="toast-title" id="toast-title">Berhasil</div>
                <div class="toast-message" id="toast-message">Profil berhasil diperbarui!</div>
            </div>
            <button type="button" class="toast-close-btn" data-bs-dismiss="toast" aria-label="Close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>
</div>

@push('scripts')
@vite(['resources/js/profile/profile.js'])
@endpush
@endsection