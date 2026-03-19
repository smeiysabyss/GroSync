<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'username' => 'owner1',
                'email'    => 'owner@toko.com',
                'password' => Hash::make('password123'),
                'role'     => 'owner',
            ],
            [
                'username' => 'admin1',
                'email'    => 'admin@toko.com',
                'password' => Hash::make('password123'),
                'role'     => 'administrator',
            ],
            [
                'username' => 'kasir1',
                'email'    => 'kasir@toko.com',
                'password' => Hash::make('password123'),
                'role'     => 'kasir',
            ],
        ]);
    }
}