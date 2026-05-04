<?php

namespace Database\Seeders;

use App\Models\Act;
use Illuminate\Database\Seeder;

class ActSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Act::factory()->count(5)
            ->randomCreatedAt()
            ->hasAppreciates(3)
            ->hasComments(3)
            ->create();
    }
}
