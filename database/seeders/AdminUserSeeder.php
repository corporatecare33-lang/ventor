<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Creates the first super admin from ADMIN_EMAIL / ADMIN_PASSWORD in .env.
 * If no password is configured, a random one is generated and printed once.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@leatherx.test');

        if (User::where('email', $email)->exists()) {
            return;
        }

        $password = env('ADMIN_PASSWORD') ?: Str::password(16, symbols: false);

        $admin = User::create([
            'name' => env('ADMIN_NAME', 'LeatherX Admin'),
            'email' => $email,
            'password' => $password,
            'is_active' => true,
        ]);
        $admin->forceFill(['email_verified_at' => now()])->save();
        $admin->assignRole('super_admin');

        if (! env('ADMIN_PASSWORD')) {
            $this->command?->warn("Super admin created: {$email} / {$password}  (change it after first login)");
        }
    }
}
