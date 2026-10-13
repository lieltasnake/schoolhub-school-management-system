<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class CreateAdminCommand extends Command
{
    protected $signature = 'app:create-admin {name} {email} {password} {--role=super-administrator}';

    protected $description = 'Create the initial school system administrator account.';

    public function handle(): int
    {
        $name = trim((string) $this->argument('name'));
        $email = strtolower(trim((string) $this->argument('email')));
        $password = (string) $this->argument('password');
        $roleSlug = strtolower(str_replace([' ', '_'], '-', trim((string) $this->option('role'))));

        if ($name === '' || $email === '' || $password === '') {
            $this->error('Name, email, and password are required.');

            return self::FAILURE;
        }

        $role = Role::firstOrCreate(
            ['slug' => $roleSlug],
            [
                'name' => ucwords(str_replace('-', ' ', $roleSlug)),
                'description' => 'System administrator role',
            ]
        );

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => $password,
                'role_id' => $role->id,
                'branch_id' => null,
                'status' => 'active',
                'locale' => 'en',
            ]
        );

        $this->info("Administrator created for {$user->email} with role {$role->name}.");

        return self::SUCCESS;
    }
}
