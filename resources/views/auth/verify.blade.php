@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Baris ini akan menengahkan konten secara vertikal dan horizontal --}}
    <div class="row justify-content-center align-items-center min-vh-100">
        {{-- Kolom ini akan mengatur lebar form. Di layar medium (md) ke atas, lebarnya 50%. Di mobile, 100%. --}}
        <div class="col-md-8 col-lg-6 col-xl-5">
            
            {{-- Menggunakan komponen card dari Bootstrap untuk tampilan yang bersih --}}
            <div class="card shadow-lg border-0">
                <div class="card-body p-4 p-md-5">
                    
                    <h1 class="h3 card-title text-center fw-bold mb-3" style="color: #82242d;">Lengkapi Data Anda</h1>
                    <p class="card-text text-center text-muted mb-4">Silakan isi data diri Anda untuk verifikasi.</p>

                    <form method="POST" action="{{ route('verification.verify') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Lengkap</label>
                            <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="division" class="form-label">Divisi</label>
                            <input id="division" type="text" name="division" class="form-control @error('division') is-invalid @enderror" value="{{ old('division') }}" required>
                            @error('division')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role / Jabatan</label>
                            <input id="role" type="text" name="role" class="form-control @error('role') is-invalid @enderror" value="{{ old('role') }}" required>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                            <select id="jenis_kelamin" name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror" required>
                                <option value="" disabled {{ old('jenis_kelamin') ? '' : 'selected' }}>Pilih Jenis Kelamin</option>
                                <option value="Cowok" {{ old('jenis_kelamin') == 'Cowok' ? 'selected' : '' }}>Cowok</option>
                                <option value="Cewek" {{ old('jenis_kelamin') == 'Cewek' ? 'selected' : '' }}>Cewek</option>
                            </select>
                            @error('jenis_kelamin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="company_code" class="form-label">Kode Unik Perusahaan</label>
                            <input id="company_code" type="text" name="company_code" class="form-control @error('company_code') is-invalid @enderror" required>
                            @error('company_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn tracking-btn btn-lg">
                                Verifikasi & Masuk
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
