<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Depends on RolePermissionSeeder having already run (needs the
     * "admin" role to exist). DatabaseSeeder enforces that order.
     */
    public function run(): void
    {
        $adminRole = Role::where('slug', 'admin')->first();

        User::updateOrCreate(
            ['email' => 'admin@irozaydeplug.com'],
            [
                'name'     => 'IROZAY Admin',
                'password' => Hash::make('ChangeMe123!'),
                'role_id'  => $adminRole?->id,
                'is_active'=> true,
            ]
        );
    }
}
