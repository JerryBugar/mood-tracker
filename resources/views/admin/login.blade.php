@extends('layouts.admin')

@section('main-content')
<style>
    .login-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 80vh;
    }
    
    .login-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        padding: 30px;
        width: 100%;
        max-width: 400px;
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
        
        <form method="POST" action="{{ route('admin.authenticate') }}">
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
            
            <button type="submit" class="btn-login">Login</button>
        </form>
    </div>
</div>
@endsection