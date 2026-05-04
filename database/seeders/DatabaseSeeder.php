<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminSeeder::class,
            TestUserSeeder::class,
            ActSeeder::class,
            AppreciateSeeder::class,
            CommentSeeder::class,
            FlagSeeder::class,
            InvitationSeeder::class,
            ContactUsSeeder::class,
        ]);
    }
}
