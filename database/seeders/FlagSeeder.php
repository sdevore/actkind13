<?php

namespace Database\Seeders;

use App\Models\Flag;
use Illuminate\Database\Seeder;

class FlagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Flag::factory()->count(5)
            ->randomCreatedAt()
            ->create();
    }
}
