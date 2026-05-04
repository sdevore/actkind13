<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $user = User::create([
            'name' => 'Sam',
            'email' => 'sdevore@sdevore.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('super-admin');
    }
}
