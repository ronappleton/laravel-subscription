<?php

declare(strict_types=1);

namespace Database\Factories;

use Appleton\Subscriptions\Enums\Status;
use Appleton\Subscriptions\Enums\TimePeriod;
use Appleton\Subscriptions\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Random\RandomException;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    /**
     * @var array<int, int>
     */
    protected array $daysOfMonth = [
        1, 2, 3, 4, 5, 6,
        7, 8, 9, 10, 11, 12,
        13, 14, 15, 16, 17, 18,
        19, 20, 21, 22, 23, 24,
        25, 26, 27, 28,
    ];

    /**
     * @throws RandomException
     */
    public function definition(): array
    {
        return [
            'action_class' => $this->faker->word(),
            'currency' => $this->faker->word(),
            'amount' => 10.00,

            'subscription_period' => TimePeriod::randomValue(),
            'subscription_period_multiplier' => 1,

            'payment_period' => TimePeriod::randomValue(),
            'payment_frequency_multiplier' => 1,

            'fixed_day_of_month' => $this->daysOfMonth[array_rand($this->daysOfMonth)],
            'allow_fixed_day_change' => $this->faker->boolean(),

            'allow_pause' => $this->faker->boolean(),
            'allow_cancel' => $this->faker->boolean(),

            'advanced_warning_days' => $this->faker->randomNumber(),
            'retry_frequency_days' => $this->faker->randomNumber(),
            'max_retries' => $this->faker->randomNumber(),

            'status' => Status::randomValue(),

            //'deleted_at' => Carbon::now(),
            'paused_at' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
