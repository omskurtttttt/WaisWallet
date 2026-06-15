<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'message' => $this->faker->sentence(10),
            'type' => $this->faker->randomElement(['budget_alert', 'goal_milestone', 'info']),
            'read_at' => null,
        ];
    }
}
