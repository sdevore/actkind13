<?php

namespace Database\Factories;

use App\Models\Act;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Comment::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'body' => fake()->text(),
            'user_id' => User::factory(),
            'act_id' => Act::factory(),
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
