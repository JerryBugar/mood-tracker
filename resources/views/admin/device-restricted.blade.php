<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Akses Dibatasi - {{ config('app.name', 'Laravel') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .restricted-container {
            background: white;
            border-radius: 20px;
            padding: 60px 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }
        .restricted-icon {
            font-size: 80px;
            color: #dc3545;
            margin-bottom: 30px;
        }
        .restricted-title {
            color: #333;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .restricted-message {
            color: #666;
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .device-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
        }
        .device-info-title {
            color: #495057;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .device-info-text {
            color: #6c757d;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="restricted-container">
        <i class="bi bi-shield-exclamation restricted-icon"></i>
        <h1 class="restricted-title">Akses Dibatasi</h1>
        <p class="restricted-message">
            Halaman admin hanya dapat diakses melalui perangkat desktop (komputer/laptop).
            <br><br>
            Silakan gunakan perangkat desktop untuk mengakses halaman ini.
        </p>
        
        <div class="device-info">
            <div class="device-info-title">
                <i class="bi bi-info-circle me-2"></i>Informasi Perangkat
            </div>
            <div class="device-info-text">
                Perangkat yang Anda gunakan saat ini tidak diizinkan untuk mengakses halaman admin.
            </div>
        </div>
    </div>
</body>
</html>

