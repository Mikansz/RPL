<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error | STEA</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .error-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 60px 40px;
            text-align: center;
            max-width: 600px;
            width: 100%;
        }
        
        .error-code {
            font-size: 8rem;
            font-weight: 700;
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 20px;
        }
        
        .error-title {
            font-size: 2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
        }
        
        .error-description {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 40px;
            line-height: 1.6;
        }
        
        .btn-home {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-size: 1rem;
            font-weight: 600;
            color: white;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn-home:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
            color: white;
            text-decoration: none;
        }
        
        .error-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 30px;
        }
        
        @media (max-width: 768px) {
            .error-container {
                padding: 40px 20px;
                margin: 20px;
            }
            
            .error-code {
                font-size: 6rem;
            }
            
            .error-title {
                font-size: 1.5rem;
            }
            
            .error-description {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <i class="fas fa-exclamation-triangle error-icon"></i>
        <div class="error-code">500</div>
        <h1 class="error-title">Server Error</h1>
        <p class="error-description">
            Maaf, terjadi kesalahan pada server. Tim teknis kami telah diberitahu 
            dan sedang bekerja untuk memperbaiki masalah ini.
        </p>
        
        <div class="mb-4">
            <p class="text-muted">Yang dapat Anda lakukan:</p>
            <ul class="list-unstyled text-start">
                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Tunggu beberapa menit dan coba lagi</li>
                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Refresh halaman ini</li>
                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Kembali ke halaman sebelumnya</li>
                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Hubungi administrator jika masalah berlanjut</li>
            </ul>
        </div>
        
        <div class="d-flex flex-column flex-sm-row gap-3 justify-content-center">
            <button onclick="location.reload()" class="btn btn-outline-secondary">
                <i class="fas fa-sync-alt me-2"></i>Refresh
            </button>
            <a href="javascript:history.back()" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
            <a href="{{ url('/') }}" class="btn-home">
                <i class="fas fa-home me-2"></i>Halaman Utama
            </a>
        </div>
        
        <div class="mt-4">
            <small class="text-muted">
                © {{ date('Y') }} STEA - Sistem Penggajian Terintegrasi
            </small>
        </div>
    </div>
</body>
</html>
