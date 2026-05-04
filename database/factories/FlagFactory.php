<?php

namespace Database\Factories;

use App\Models\Act;
use App\Models\Comment;
use App\Models\Flag;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FlagFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Flag::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $flaggable = fake()
            ->randomElement([Act::factory(), Comment::factory()])
            ->create();

        return [
            'reason' => fake()->text(),
            'user_id' => User::factory(),
            'flaggable_id' => $flaggable->id,
            'flaggable_type' => $flaggable->getMorphClass(),
            'flagged_user_id' => $flaggable->user_id,
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
