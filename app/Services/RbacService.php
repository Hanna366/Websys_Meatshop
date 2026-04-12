<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class RbacService
{
    /**
     * Define role-based permissions
     */
    private static $permissions = [
        'owner' => [
            'can_manage_users' => true,
            'can_manage_inventory' => true,
            'can_process_sales' => true,
            'can_view_reports' => true,
            'can_manage_suppliers' => true,
            'can_manage_customers' => true,
            'can_export_data' => true,
            'can_access_api' => true,
            'can_manage_settings' => true,
            'can_manage_subscriptions' => true,
        ],
        'manager' => [
            'can_manage_users' => false,
            'can_manage_inventory' => true,
            'can_process_sales' => true,
            'can_view_reports' => true,
            'can_manage_suppliers' => true,
            'can_manage_customers' => true,
            'can_export_data' => true,
            'can_access_api' => false,
            'can_manage_settings' => false,
            'can_manage_subscriptions' => false,
        ],
        'cashier' => [
            'can_manage_users' => false,
            'can_manage_inventory' => false,
            'can_process_sales' => true,
            'can_view_reports' => false,
            'can_manage_suppliers' => false,
            'can_manage_customers' => true,
            'can_export_data' => false,
            'can_access_api' => false,
            'can_manage_settings' => false,
            'can_manage_subscriptions' => false,
        ],
        'inventory_staff' => [
            'can_manage_users' => false,
            'can_manage_inventory' => true,
            'can_process_sales' => false,
            'can_view_reports' => false,
            'can_manage_suppliers' => false,
            'can_manage_customers' => false,
            'can_export_data' => false,
            'can_access_api' => false,
            'can_manage_settings' => false,
            'can_manage_subscriptions' => false,
        ],
    ];

    /**
     * Get all permissions for a role
     */
    public static function getRolePermissions(string $role): array
    {
        return self::$permissions[$role] ?? [];
    }

    /**
     * Check if a role has a specific permission
     */
    public static function roleHasPermission(string $role, string $permission): bool
    {
        return (self::$permissions[$role][$permission] ?? false) === true;
    }

    /**
     * Check if a user has a specific permission
     */
    public static function userHasPermission(User $user, string $permission): bool
    {
        // Check user-specific permissions first
        $userPermissions = $user->permissions ?? [];
        if (isset($userPermissions[$permission])) {
            return $userPermissions[$permission] === true;
        }

        // Map modular permissions (module.action) to legacy role flags or role names
        $map = [
            // Sales
            'sales.view' => ['flags' => ['can_process_sales'], 'roles' => []],
            'sales.create' => ['flags' => ['can_process_sales'], 'roles' => []],
            'sales.refund' => ['flags' => [], 'roles' => ['owner', 'manager']],

            // Inventory
            'inventory.view' => ['flags' => ['can_manage_inventory', 'can_process_sales'], 'roles' => []],
            'inventory.add_stock' => ['flags' => ['can_manage_inventory'], 'roles' => []],
            'inventory.update_stock' => ['flags' => ['can_manage_inventory'], 'roles' => []],
            'inventory.adjust_stock' => ['flags' => ['can_manage_inventory'], 'roles' => []],

            // Products
            'products.view' => ['flags' => ['can_manage_inventory', 'can_process_sales'], 'roles' => []],
            'products.create' => ['flags' => ['can_manage_inventory'], 'roles' => []],
            'products.update' => ['flags' => ['can_manage_inventory'], 'roles' => []],
            'products.delete' => ['flags' => ['can_manage_inventory'], 'roles' => []],

            // Users
            'users.view' => ['flags' => ['can_manage_users'], 'roles' => []],
            'users.create' => ['flags' => ['can_manage_users'], 'roles' => []],
            'users.assign_roles' => ['flags' => ['can_manage_users'], 'roles' => []],

            // Settings
            'settings.manage' => ['flags' => ['can_manage_settings'], 'roles' => []],
        ];

        if (isset($map[$permission])) {
            $entry = $map[$permission];

            // Check flags (legacy boolean permission keys)
            foreach ($entry['flags'] as $flag) {
                if (self::roleHasPermission($user->role, $flag)) {
                    return true;
                }
            }

            // Check explicit allowed roles
            if (!empty($entry['roles']) && in_array($user->role, $entry['roles'], true)) {
                return true;
            }

            return false;
        }

        // Fallback: if legacy flag with same name exists, check it
        return self::roleHasPermission($user->role, $permission);
    }

    /**
     * Get all available roles
     */
    public static function getAvailableRoles(): array
    {
        return array_keys(self::$permissions);
    }

    /**
     * Get role display name
     */
    public static function getRoleDisplayName(string $role): string
    {
        $displayNames = [
            'owner' => 'Owner',
            'manager' => 'Manager',
            'cashier' => 'Cashier',
            'inventory_staff' => 'Inventory Staff',
        ];

        return $displayNames[$role] ?? ucfirst($role);
    }

    /**
     * Get role description
     */
    public static function getRoleDescription(string $role): string
    {
        $descriptions = [
            'owner' => 'Full access to all system features and settings',
            'manager' => 'Can manage inventory, sales, reports, and staff (except users)',
            'cashier' => 'Can process sales and manage customers only',
            'inventory_staff' => 'Can manage inventory and stock levels only',
        ];

        return $descriptions[$role] ?? 'No description available';
    }

    /**
     * Get role hierarchy level (higher number = more privileges)
     */
    public static function getRoleHierarchy(string $role): int
    {
        $hierarchy = [
            'owner' => 4,
            'manager' => 3,
            'inventory_staff' => 2,
            'cashier' => 1,
        ];

        return $hierarchy[$role] ?? 0;
    }

    /**
     * Check if a user can manage another user
     */
    public static function canManageUser(User $manager, User $target): bool
    {
        // Cannot manage yourself
        if ($manager->id === $target->id) {
            return false;
        }

        // Must be in same tenant
        if ($manager->tenant_id !== $target->tenant_id) {
            return false;
        }

        // Owner can manage everyone
        if ($manager->role === 'owner') {
            return true;
        }

        // Manager can manage cashiers and inventory staff
        if ($manager->role === 'manager') {
            return in_array($target->role, ['cashier', 'inventory_staff']);
        }

        // Others cannot manage users
        return false;
    }

    /**
     * Get user's accessible menu items based on role
     */
    public static function getUserMenuItems(User $user): array
    {
        $permissions = self::getRolePermissions($user->role);
        
        $menuItems = [];
        
        if ($permissions['can_process_sales']) {
            $menuItems[] = [
                'name' => 'Sales',
                'icon' => 'fas fa-cash-register',
                'route' => 'sales.index',
                'description' => 'Process sales transactions'
            ];
        }
        
        if ($permissions['can_manage_customers']) {
            $menuItems[] = [
                'name' => 'Customers',
                'icon' => 'fas fa-users',
                'route' => 'customers.index',
                'description' => 'Manage customer information'
            ];
        }
        
        if ($permissions['can_manage_inventory']) {
            $menuItems[] = [
                'name' => 'Inventory',
                'icon' => 'fas fa-boxes',
                'route' => 'inventory.index',
                'description' => 'Manage stock and products'
            ];
        }
        
        if ($permissions['can_manage_suppliers']) {
            $menuItems[] = [
                'name' => 'Suppliers',
                'icon' => 'fas fa-truck',
                'route' => 'suppliers.index',
                'description' => 'Manage supplier information'
            ];
        }
        
        if ($permissions['can_view_reports']) {
            $menuItems[] = [
                'name' => 'Reports',
                'icon' => 'fas fa-chart-bar',
                'route' => 'reports.index',
                'description' => 'View business reports'
            ];
        }
        
        if ($permissions['can_manage_users']) {
            $menuItems[] = [
                'name' => 'Users',
                'icon' => 'fas fa-user-cog',
                'route' => 'users.index',
                'description' => 'Manage user accounts'
            ];
        }
        
        if ($permissions['can_manage_settings']) {
            $menuItems[] = [
                'name' => 'Settings',
                'icon' => 'fas fa-cog',
                'route' => 'settings.index',
                'description' => 'System settings'
            ];
        }
        
        return $menuItems;
    }

    /**
     * Validate role assignment based on current user's role
     */
    public static function canAssignRole(User $assigner, string $targetRole): bool
    {
        // Owner can assign any role
        if ($assigner->role === 'owner') {
            return true;
        }

        // Manager can only assign cashier and inventory staff roles
        if ($assigner->role === 'manager') {
            return in_array($targetRole, ['cashier', 'inventory_staff']);
        }

        // Others cannot assign roles
        return false;
    }

    /**
     * Get role statistics for a tenant
     */
    public static function getRoleStats(string $tenantId): array
    {
        $stats = [];
        
        foreach (self::getAvailableRoles() as $role) {
            $count = User::where('tenant_id', $tenantId)
                ->where('role', $role)
                ->where('status', 'active')
                ->count();
                
            $stats[$role] = [
                'count' => $count,
                'display_name' => self::getRoleDisplayName($role),
                'description' => self::getRoleDescription($role),
            ];
        }
        
        return $stats;
    }
}
