<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class LogoController extends Controller
{
    /**
     * Serve logo images with proper cache headers
     * 
     * @param string $filename
     * @return \Illuminate\Http\Response
     */
    public function serve($filename)
    {
        $logoPath = public_path('logo/' . $filename);
        
        // Validasi file yang diizinkan untuk keamanan
        $allowedFiles = [
            'favicons.png',
            'google.png',
            'love.png',
            'netral.png',
            'netral1.png',
            'senyum.png',
            'senyum1.png',
            'sedih.png',
            'sedih1.png',
            'lelah.png',
            'lelah1.png',
            'marah.png',
            'marah1.png',
            'icon.jpeg'
        ];
        
        if (!in_array($filename, $allowedFiles) || !file_exists($logoPath)) {
            abort(404);
        }
        
        $file = file_get_contents($logoPath);
        $mimeType = mime_content_type($logoPath);
        
        // Set cache headers untuk browser caching (1 tahun)
        $headers = [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000, immutable', // 1 tahun
            'Expires' => gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT',
            'ETag' => md5_file($logoPath),
            'Last-Modified' => gmdate('D, d M Y H:i:s', filemtime($logoPath)) . ' GMT',
        ];
        
        return response($file, 200, $headers);
    }
}

