<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Buat Roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'direktur']);
        Role::create(['name' => 'keuangan']);
        Role::create(['name' => 'hrd']);
        Role::create(['name' => 'marketing']);
        Role::create(['name' => 'redaksi']);

        // Buat User Admin Default
        $user = User::create([
            'name' => 'Taufik',
            'email' => 'mtaufikxxx@gmail.com',
            'password' => Hash::make('katasandiku'), // Ganti 'password' dengan password yang aman
        ]);
        
        // Berikan role 'admin' ke user tersebut
        $user->assignRole('admin');
    }
}