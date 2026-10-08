<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'pos.access'            => 'Access POS / make sales',
            'sales.view'            => 'View sales records',
            'inventory.view'        => 'View inventory / product availability',
            'inventory.manage'      => 'Create/edit products, stock adjustments (sensitive inventory ops)',
            'purchases.view'        => 'View purchases',
            'purchases.manage'      => 'Create/receive purchases',
            'customers.view'        => 'View customers',
            'customers.manage'      => 'Create/edit customers',
            'suppliers.view'        => 'View suppliers',
            'suppliers.manage'      => 'Create/edit suppliers',
            'expenses.view'         => 'View expenses',
            'expenses.manage'       => 'Record expenses',
            'reports.view'          => 'View operational reports (sales, inventory, purchases)',
            'reports.financial.view'=> 'View financial reports (profit, net profit)',
            'returns.process'       => 'Process returns/refunds',
            'users.manage'          => 'Manage users and roles',
            'settings.manage'       => 'Manage business settings',
        ];

        foreach ($permissions as $slug => $name) {
            Permission::updateOrCreate(['slug' => $slug], ['name' => $name]);
        }

        $roleMap = [
            'admin' => [
                'name' => 'Admin',
                'permissions' => array_keys($permissions), // everything
            ],
            'manager' => [
                'name' => 'Manager',
                'permissions' => [
                    'pos.access', 'sales.view',
                    'inventory.view', 'inventory.manage',
                    'purchases.view', 'purchases.manage',
                    'customers.view', 'customers.manage',
                    'suppliers.view', 'suppliers.manage',
                    'expenses.view', 'expenses.manage',
                    'reports.view', 'reports.financial.view',
                    'returns.process',
                ],
            ],
            'sales-staff' => [
                'name' => 'Sales Staff',
                'permissions' => [
                    'pos.access',
                    'sales.view',
                    'customers.view', 'customers.manage',
                    'inventory.view', // product availability only — not inventory.manage
                ],
            ],
        ];

        foreach ($roleMap as $slug => $data) {
            $role = Role::updateOrCreate(['slug' => $slug], ['name' => $data['name']]);
            $permissionIds = Permission::whereIn('slug', $data['permissions'])->pluck('id');
            $role->permissions()->sync($permissionIds);
        }
    }
}
