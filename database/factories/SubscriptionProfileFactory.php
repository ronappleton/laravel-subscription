<?php

declare(strict_types=1);

namespace Database\Factories;

use Appleton\LaravelWallet\Enums\Currency;
use Appleton\Subscriptions\Enums\TimePeriod;
use Appleton\Subscriptions\Models\SubscriptionProfile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Random\RandomException;

class SubscriptionProfileFactory extends Factory
{
    protected $model = SubscriptionProfile::class;

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
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'currency' => Currency::cases()[array_rand(Currency::cases())]->value,
            'amount' => $this->faker->randomFloat(),
            'subscription_period' => TimePeriod::random(),
            'subscription_period_multiplier' => $this->faker->randomNumber(),
            'payment_period' => TimePeriod::random(),
            'payment_frequency_multiplier' => $this->faker->randomNumber(),
            'fixed_day_of_month' => $this->daysOfMonth[array_rand($this->daysOfMonth)],
            'allow_fixed_day_change' => $this->faker->boolean(),
            'allow_pause' => $this->faker->boolean(),
            'allow_cancel' => $this->faker->boolean(),
            'advanced_warning_days' => $this->faker->randomNumber(),
            'retry_frequency_days' => $this->faker->randomNumber(),
            'max_retries' => $this->faker->randomNumber(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
