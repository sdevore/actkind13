<?php

namespace Database\Factories;

use App\Enums\ActType;
use App\Models\Act;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Act::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraphs(3, true),
            'type' => fake()->randomElement(ActType::cases()),
            'user_id' => User::factory(),
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
