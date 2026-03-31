<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LogoHelper
{
    /**
     * Get tenant logo URL or generate dynamic logo
     */
    public static function getTenantLogo($tenant = null, $size = 64)
    {
        // If no tenant provided, try to get current tenant
        if (!$tenant && function_exists('tenant')) {
            $tenant = tenant();
        }
        
        // If still no tenant, return default logo
        if (!$tenant) {
            return self::getDefaultLogo($size);
        }
        
        // Check if tenant has custom logo
        if ($tenant->logo_path && Storage::disk('public')->exists($tenant->logo_path)) {
            return Storage::disk('public')->url($tenant->logo_path);
        }
        
        // Generate dynamic logo based on business name
        return self::generateDynamicLogo($tenant->business_name, $size);
    }
    
    /**
     * Generate dynamic logo using text-based approach
     */
    public static function generateDynamicLogo($businessName, $size = 64)
    {
        $businessName = trim($businessName);
        $initials = self::getBusinessInitials($businessName);
        $colors = self::getBusinessColors($businessName);
        
        // Create SVG logo
        $svg = self::createTextLogo($initials, $colors, $size);
        
        // Convert to data URL
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
    
    /**
     * Get business initials from name
     */
    public static function getBusinessInitials($businessName)
    {
        $words = preg_split('/[\s\-_]+/', $businessName);
        
        if (count($words) >= 2) {
            // Take first letter of first two words
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        } else {
            // Take first two letters of single word
            return strtoupper(substr($businessName, 0, 2));
        }
    }
    
    /**
     * Get consistent colors based on business name
     */
    private static function getBusinessColors($businessName)
    {
        $hash = crc32($businessName);
        
        // Define color palettes
        $palettes = [
            ['#dc3545', '#ffffff'], // Red
            ['#007bff', '#ffffff'], // Blue
            ['#28a745', '#ffffff'], // Green
            ['#ffc107', '#212529'], // Yellow
            ['#6f42c1', '#ffffff'], // Purple
            ['#fd7e14', '#ffffff'], // Orange
            ['#20c997', '#ffffff'], // Teal
            ['#6c757d', '#ffffff'], // Gray
        ];
        
        $paletteIndex = abs($hash) % count($palettes);
        return $palettes[$paletteIndex];
    }
    
    /**
     * Create SVG text logo
     */
    private static function createTextLogo($text, $colors, $size)
    {
        $bgColor = $colors[0];
        $textColor = $colors[1];
        $fontSize = $size * 0.4;
        
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$size}" height="{$size}" viewBox="0 0 {$size} {$size}">
    <rect width="{$size}" height="{$size}" fill="{$bgColor}" rx="12"/>
    <text x="50%" y="50%" 
          font-family="Arial, sans-serif" 
          font-size="{$fontSize}" 
          font-weight="bold" 
          fill="{$textColor}" 
          text-anchor="middle" 
          dominant-baseline="middle">
        {$text}
    </text>
</svg>
SVG;
    }
    
    /**
     * Get default logo for central app
     */
    public static function getDefaultLogo($size = 64)
    {
        $fontSize = $size * 0.3;
        
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$size}" height="{$size}" viewBox="0 0 {$size} {$size}">
    <rect width="{$size}" height="{$size}" fill="#dc3545" rx="12"/>
    <text x="50%" y="50%" 
          font-family="Arial, sans-serif" 
          font-size="{$fontSize}" 
          font-weight="bold" 
          fill="white" 
          text-anchor="middle" 
          dominant-baseline="middle">
        MS
    </text>
</svg>
SVG;
        
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
    
    /**
     * Get tenant business name for display
     */
    public static function getTenantBusinessName($tenant = null)
    {
        if (!$tenant && function_exists('tenant')) {
            $tenant = tenant();
        }
        
        return $tenant ? $tenant->business_name : 'MeatShop POS';
    }
}
