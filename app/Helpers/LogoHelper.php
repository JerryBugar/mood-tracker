<?php

namespace App\Helpers;

class LogoHelper
{
    /**
     * Get logo URL with caching support
     * 
     * @param string $filename
     * @return string
     */
    public static function url(string $filename): string
    {
        // Gunakan route dengan cache headers jika tersedia
        if (route('logo.serve', ['filename' => $filename], false)) {
            return route('logo.serve', ['filename' => $filename]);
        }
        
        // Fallback ke asset biasa
        return asset('logo/' . $filename);
    }
    
    /**
     * Get all logo filenames that should be preloaded
     * 
     * @return array
     */
    public static function getPreloadLogos(): array
    {
        return [
            'favicons.png',
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
            'google.png',
            'love.png'
        ];
    }
}

