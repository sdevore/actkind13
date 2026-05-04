<?php

namespace Database\Seeders;

use App\Models\Act;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regular = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'regular@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin = User::factory()->create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $moderator = User::factory()->create([
            'name' => 'Moderator',
            'email' => 'moderator@example.com',
            'password' => bcrypt('password'),
        ]);
        $regular->givePermissionTo('invite users');
        Invitation::factory()
            ->state(['user_id' => $regular->id])
            ->randomCreatedAt()
            ->count(5)->create();
        Act::factory()
            ->state(['user_id' => $regular->id])
            ->randomCreatedAt()
            ->hasComments(5)
            ->hasAppreciates(5)
            ->count(5)->create();
        $moderator->assignRole('moderator');

        $admin->assignRole('administrator');
    }
}
