<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RoleSeeder extends Seeder
{
    
    public function run(): void
    {
        Role::query()->delete();

        $superadmin = Role::create(['name' => 'superadmin']);
        $admin = Role::create(['name' => 'admin']);

        $user = User::where('email', 'akmal@admin.com')->first();
        if($user) {
            $user->syncRoles(['superadmin']);
        }
    }
}
