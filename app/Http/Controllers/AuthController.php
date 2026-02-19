<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Register a new tenant and owner.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'business_name' => 'required|string|max:255',
            'business_email' => 'required|email|unique:tenants,business_email',
            'business_phone' => 'required|string|max:20',
            'business_address.street' => 'required|string|max:255',
            'business_address.city' => 'required|string|max:255',
            'business_address.state' => 'required|string|max:255',
            'business_address.zip_code' => 'required|string|max:20',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            \DB::beginTransaction();

            // Generate unique tenant ID
            $tenantId = 'TEN' . strtoupper(Str::random(8));

            // Create tenant
            $tenant = Tenant::create([
                'tenant_id' => $tenantId,
                'business_name' => $request->business_name,
                'business_email' => $request->business_email,
                'business_phone' => $request->business_phone,
                'business_address' => $request->business_address,
                'subscription' => [
                    'plan' => 'basic',
                    'status' => 'trial',
                    'start_date' => now(),
                    'end_date' => now()->addDays(30),
                    'monthly_price' => 29,
                    'features' => [
                        ['name' => 'inventory_tracking', 'enabled' => true]
                    ]
                ],
                'limits' => [
                    'max_users' => 1,
                    'max_products' => 100,
                    'max_storage_mb' => 1000,
                    'max_api_calls_per_month' => 1000
                ],
                'usage' => [
                    'users_count' => 0,
                    'products_count' => 0,
                    'storage_used' => 0,
                    'api_calls_this_month' => 0
                ],
                'settings' => [
                    'currency' => 'USD',
                    'weight_unit' => 'lb',
                    'tax_rate' => 0,
                    'low_stock_threshold' => 10,
                    'expiry_warning_days' => 7,
                    'enable_sms_notifications' => false,
                    'enable_email_notifications' => true
                ],
                'status' => 'active'
            ]);

            // Create owner user
            $user = User::create([
                'tenant_id' => $tenantId,
                'username' => $request->email,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'owner',
                'profile' => [
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'phone' => $request->phone
                ],
                'permissions' => [
                    'can_manage_users' => true,
                    'can_manage_inventory' => true,
                    'can_process_sales' => true,
                    'can_view_reports' => true,
                    'can_manage_suppliers' => true,
                    'can_manage_customers' => true,
                    'can_export_data' => true,
                    'can_access_api' => true
                ],
                'status' => 'active'
            ]);

            // Update tenant usage
            $tenant->usage['users_count'] = 1;
            $tenant->save();

            \DB::commit();

            // Generate token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                        'role' => $user->role,
                        'profile' => $user->profile
                    ],
                    'tenant' => [
                        'tenant_id' => $tenant->tenant_id,
                        'business_name' => $tenant->business_name,
                        'subscription' => $tenant->subscription
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Registration failed'
            ], 500);
        }
    }

    /**
     * Login user and return token.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 400);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password'
            ], 401);
        }

        $user = Auth::user();
        
        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Account is not active'
            ], 401);
        }

        // Check tenant status
        $tenant = Tenant::where('tenant_id', $user->tenant_id)->first();
        if (!$tenant || $tenant->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Tenant account is not active'
            ], 401);
        }

        // Check subscription status
        if (in_array($tenant->subscription['status'], ['suspended', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription is not active'
            ], 403);
        }

        // Reset login attempts
        $user->login_attempts = 0;
        $user->last_login = now();
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                    'profile' => $user->profile,
                    'permissions' => $user->permissions
                ],
                'tenant' => [
                    'tenant_id' => $tenant->tenant_id,
                    'business_name' => $tenant->business_name,
                    'subscription' => $tenant->subscription,
                    'settings' => $tenant->settings
                ]
            ]
        ]);
    }

    /**
     * Logout user.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Logout successful'
        ]);
    }

    /**
     * Get authenticated user profile.
     */
    public function profile(Request $request)
    {
        $user = $request->user()->load('tenant');
        
        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user
            ]
        ]);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile.first_name' => 'sometimes|string|max:255',
            'profile.last_name' => 'sometimes|string|max:255',
            'profile.phone' => 'sometimes|string|max:20',
            'preferences.language' => 'sometimes|in:en,es,fr',
            'preferences.timezone' => 'sometimes|string|max:255',
            'preferences.theme' => 'sometimes|in:light,dark',
            'preferences.email_notifications' => 'sometimes|boolean',
            'preferences.sms_notifications' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 400);
        }

        $user = $request->user();
        
        if ($request->has('profile')) {
            $user->profile = array_merge($user->profile ?? [], $request->profile);
        }
        
        if ($request->has('preferences')) {
            $user->preferences = array_merge($user->preferences ?? [], $request->preferences);
        }
        
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'user' => $user
            ]
        ]);
    }

    /**
     * Change password.
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 400);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect'
            ], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * Refresh token.
     */
    public function refresh(Request $request)
    {
        $user = $request->user();
        
        // Revoke current token
        $request->user()->currentAccessToken()->delete();
        
        // Create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'token' => $token
            ]
        ]);
    }
}
