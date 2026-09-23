<?php

namespace Database\Factories;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InvitationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Invitation::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $date = fake()->randomElement([null,  fake()->dateTimeBetween('-3 months', 'now')]);
        $joined = null;
        if ($date !== null) {
            $joined = User::factory();
        }

        $test = [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'code' => Str::random(10),
            'message' => fake()->paragraphs(2, true),
            'joined_at' => $date,
            'user_id' => User::factory(),
            'joined_id' => fn () => $joined,
        ];

        return $test;
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

    public function unused(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'joined_at' => null,
            'joined_id' => null,
        ]);
    }

    public function joined(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'joined_at' => now(),
            'joined_id' => User::factory(),
        ]);
    }
}
