<?php

namespace App\Http\Controllers;

use App\Helpers\LogoHelper;
use App\Models\Tenant;
use Illuminate\Http\Request;

class LogoController extends Controller
{
    /**
     * Generate tenant logo
     */
    public function generateLogo(Request $request, $tenantId = null)
    {
        $tenant = null;
        
        if ($tenantId) {
            $tenant = Tenant::find($tenantId);
        } elseif (function_exists('tenant')) {
            $tenant = tenant();
        }
        
        $businessName = $tenant ? $tenant->business_name : 'MeatShop POS';
        $size = $request->get('size', 64);
        
        $logoUrl = LogoHelper::generateDynamicLogo($businessName, $size);
        
        return response()->json([
            'business_name' => $businessName,
            'logo_url' => $logoUrl,
            'tenant_id' => $tenant ? $tenant->id : null,
            'domain' => $tenant ? $tenant->domain : null,
        ]);
    }
    
    /**
     * Test different business names
     */
    public function testLogos()
    {
        $testNames = [
            'Hanna Meat Shop',
            'Rusty Carson',
            'Buksu Business',
            'Kitayama Retail',
            'Test Store',
            'ABC Company',
            'XYZ Corporation'
        ];
        
        $results = [];
        
        foreach ($testNames as $name) {
            $results[] = [
                'business_name' => $name,
                'logo_url' => LogoHelper::generateDynamicLogo($name, 64),
                'initials' => LogoHelper::getBusinessInitials($name)
            ];
        }
        
        return response()->json($results);
    }
}
