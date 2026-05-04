<?php

namespace Database\Seeders;

use App\Models\Appreciate;
use Illuminate\Database\Seeder;

class AppreciateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Appreciate::factory()
            ->randomCreatedAt()
            ->count(5)
            ->create();
    }
}
