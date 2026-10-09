<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

#[\Illuminate\Console\Attributes\Signature('app:create-admin-account')]
#[\Illuminate\Console\Attributes\Description('Create a new administrator account')]
class CreateAdminAccount extends Command
{
    public function handle(): int
    {
        $email = 'info@irozaydeplug.com';

        if (User::where('email', $email)->exists()) {
            $this->info('Account already exists. No changes made.');
            return self::SUCCESS;
        }

        $adminRole = Role::where('slug', 'admin')->first();

        if (!$adminRole) {
            $this->error('Admin role not found. No account created.');
            return self::FAILURE;
        }

        $password = env('NEW_ADMIN_PASSWORD');

        if (!is_string($password) || strlen($password) < 12) {
            $this->error('Set NEW_ADMIN_PASSWORD to a password of at least 12 characters.');
            return self::FAILURE;
        }

        User::create([
            'name' => 'IROZAY Admin',
            'email' => $email,
            'password' => Hash::make($password),
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);

        $this->info('New administrator account created successfully.');

        return self::SUCCESS;
    }
}