<?php

namespace Database\Factories;

use App\Models\Act;
use App\Models\Appreciate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppreciateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Appreciate::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'appreciable_id' => Act::factory(),
            'appreciable_type' => (new Act)->getMorphClass(),
        ];
    }

    /**
     * @return Factory picks a created date in the last 3 months
     */
    public function randomCreatedAt(): Factory
    {
        return $this->state(function (array $attributes) {
            return ['created_at' => fake()->dateTimeBetween('-3 months', 'now')];
        });
    }
}
