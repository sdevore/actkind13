<?php

namespace Database\Seeders;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class InvitationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Invitation::factory()
            ->state(new Sequence(
                ['user_id' => 1],
                ['user_id' => 2],
                fn () => ['user_id' => User::factory()]
            ))
            ->randomCreatedAt()
            ->count(20)->create();
    }
}
