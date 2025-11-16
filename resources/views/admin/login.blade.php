@extends('layouts.admin')

@section('main-content')
<style>
    .login-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 80vh;
        position: relative;
        z-index: 10;
    }

    .login-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 30px;
        width: 100%;
        max-width: 400px;
        position: relative;
        z-index: 20;
    }

    .login-title {
        text-align: center;
        color: #82272c;
        margin-bottom: 20px;
        font-size: 1.8rem;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
        color: #495057;
    }

    .form-control {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        font-size: 1rem;
    }

    .btn-login {
        background-color: #661118ff;
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 8px;
        font-size: 1rem;
        cursor: pointer;
        width: 100%;
        margin-top: 10px;
        position: relative;
        z-index: 30;
    }

    .btn-login:hover {
        background-color: #83282f;
    }

    .error-message {
        color: #dc3545;
        font-size: 0.875rem;
        margin-top: 5px;
    }
</style>

<div class="login-container">
    <div class="login-card">
        <h2 class="login-title">Admin Login</h2>

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.authenticate') }}" id="adminLoginForm">
            @csrf
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>

            @if($errors->any())
                <div class="error-message">
                    {{ $errors->first('credentials') }}
                </div>
            @endif

            @if(isset($rateLimitBlocked) && $rateLimitBlocked)
                <div class="alert alert-danger mt-2" id="rate-limit-alert">
                    <strong>Akun Terblokir!</strong><br>
                    Terlalu banyak percobaan. Silakan coba lagi dalam <span id="countdown-timer">{{ ceil($rateLimitSeconds / 60) }}</span> menit.
                </div>
            @endif

            <button type="submit" class="btn-login" id="submit-btn">Login</button>
        </form>
    </div>
</div>

<script>
    // Nonaktifkan Turbo untuk form ini secara eksplisit
    document.addEventListener('DOMContentLoaded', function() {
        const loginForm = document.getElementById('adminLoginForm');
        const submitBtn = document.getElementById('submit-btn');
        const rateLimitAlert = document.getElementById('rate-limit-alert');
        let countdownTimer = document.getElementById('countdown-timer');
        const formInputs = loginForm ? loginForm.querySelectorAll('input, button') : [];

        // Rate limit status dari server
        let rateLimitBlocked = {{ isset($rateLimitBlocked) && $rateLimitBlocked ? 'true' : 'false' }};
        let rateLimitSeconds = {{ isset($rateLimitSeconds) ? $rateLimitSeconds : 0 }};
        const rateLimitRemaining = {{ isset($rateLimitRemaining) ? $rateLimitRemaining : 3 }};

        // Check error message dari server untuk rate limit
        const errorMessage = document.querySelector('.error-message');
        if (errorMessage && errorMessage.textContent.includes('Terlalu banyak percobaan')) {
            rateLimitBlocked = true;
            // Extract minutes from error message
            const match = errorMessage.textContent.match(/(\d+)\s*menit/);
            if (match) {
                rateLimitSeconds = parseInt(match[1]) * 60;
            }
        }

        // Fungsi untuk disable form saat rate limit
        function disableForm() {
            if (rateLimitBlocked || rateLimitSeconds > 0) {
                formInputs.forEach(el => {
                    el.disabled = true;
                    el.style.opacity = '0.6';
                    el.style.cursor = 'not-allowed';
                });
                if (submitBtn) {
                    submitBtn.textContent = 'Form Terblokir - Silakan Tunggu';
                }
            }
        }

        // Fungsi untuk update countdown timer
        function updateCountdown() {
            if (rateLimitSeconds > 0 && countdownTimer) {
                const minutes = Math.ceil(rateLimitSeconds / 60);
                countdownTimer.textContent = minutes;
                rateLimitSeconds--;
                
                if (rateLimitSeconds <= 0) {
                    // Reload halaman setelah countdown selesai
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            }
        }

        // Initialize
        disableForm();
        if (rateLimitBlocked && rateLimitSeconds > 0) {
            // Show alert jika belum ada
            if (!rateLimitAlert && rateLimitSeconds > 0) {
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger mt-2';
                alertDiv.id = 'rate-limit-alert';
                alertDiv.innerHTML = '<strong>Akun Terblokir!</strong><br>Terlalu banyak percobaan. Silakan coba lagi dalam <span id="countdown-timer">' + Math.ceil(rateLimitSeconds / 60) + '</span> menit.';
                const submitBtnParent = submitBtn.parentElement;
                submitBtnParent.insertBefore(alertDiv, submitBtn);
                countdownTimer = document.getElementById('countdown-timer');
            }
            setInterval(updateCountdown, 1000);
        }
        
        if (loginForm) {
            // Cegah Turbo dari mengambil alih form ini
            loginForm.setAttribute('data-turbo', 'false');
            
            // Tambahkan event listener untuk prevent submission jika blocked
            loginForm.addEventListener('submit', function(e) {
                // Prevent submission jika rate limit blocked
                if (rateLimitBlocked || rateLimitSeconds > 0) {
                    e.preventDefault();
                    e.stopPropagation();
                    alert('Form terblokir. Silakan tunggu beberapa saat.');
                    return false;
                }
            });
        }
    });
</script>
@endsection