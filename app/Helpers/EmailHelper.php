<?php

namespace App\Helpers;

class EmailHelper
{
    /**
     * Get business email alias based on type and business name.
     */
    public static function getBusinessEmail(string $type, string $businessName): string
    {
        $cleanName = strtolower(str_replace([' ', '-', '_'], '', $businessName));
        
        $aliases = [
            'main' => "+{$cleanName}@gmail.com",
            'owner' => "+{$cleanName}-owner@gmail.com", 
            'manager' => "+{$cleanName}-manager@gmail.com",
            'cashier' => "+{$cleanName}-cashier@gmail.com",
            'inventory' => "+{$cleanName}-inventory@gmail.com",
            'support' => "+{$cleanName}-support@gmail.com",
            'billing' => "+{$cleanName}-billing@gmail.com",
            'info' => "+{$cleanName}-info@gmail.com"
        ];
        
        $email = $aliases[$type] ?? $aliases['main'];
        
        // Auto-append localhost for local development
        if (app()->environment('local') && !str_contains($email, 'localhost')) {
            $email = str_replace('@gmail.com', '.localhost', $email);
        }
        
        return $email;
    }
    
    /**
     * Get all business email aliases for a business.
     */
    public static function getAllBusinessEmails(string $businessName): array
    {
        $cleanName = strtolower(str_replace([' ', '-', '_'], '', $businessName));
        
        $aliases = [
            'main' => "+{$cleanName}@gmail.com",
            'owner' => "+{$cleanName}-owner@gmail.com",
            'manager' => "+{$cleanName}-manager@gmail.com", 
            'cashier' => "+{$cleanName}-cashier@gmail.com",
            'inventory' => "+{$cleanName}-inventory@gmail.com",
            'support' => "+{$cleanName}-support@gmail.com",
            'billing' => "+{$cleanName}-billing@gmail.com",
            'info' => "+{$cleanName}-info@gmail.com"
        ];
        
        // Auto-append localhost for local development
        if (app()->environment('local')) {
            foreach ($aliases as $key => $email) {
                if (!str_contains($email, 'localhost')) {
                    $aliases[$key] = str_replace('@gmail.com', '.localhost', $email);
                }
            }
        }
        
        return $aliases;
    }
}
