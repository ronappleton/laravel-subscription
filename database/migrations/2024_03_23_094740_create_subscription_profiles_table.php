<?php

declare(strict_types=1);

use Appleton\LaravelWallet\Enums\Currency;
use Appleton\Subscriptions\Enums\{TimePeriod, Status};
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(config()->string('subscriptions.table_names.subscription_profiles'), function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable();

            $table->json('name')->nullable();
            $table->json('description')->nullable();

            /**
             * Preset currency and value of subscription.
             */
            $table->enum('currency', array_column(Currency::cases(), 'value'))
                ->nullable();
            $table->decimal('amount', 18, 10)
                ->nullable();

            $table->enum('subscription_period', TimePeriod::values());
            $table->tinyInteger('subscription_period_multiplier', false, true)
                ->default(1);

            $table->enum('payment_period', TimePeriod::values());
            $table->tinyInteger('payment_frequency_multiplier', false, true)
                ->default(1);

            $table->enum('fixed_day_of_month', [
                '1', '2', '3', '4', '5', '6',
                '7', '8', '9', '10', '11', '12',
                '13', '14', '15', '16', '17', '18',
                '19', '20', '21', '22', '23', '24',
                '25', '26', '27', '28',
            ]);
            $table->boolean('allow_fixed_day_change')->default(false);

            $table->boolean('allow_pause')->default(false);
            $table->boolean('allow_cancel')->default(false);

            $table->unsignedTinyInteger('advanced_warning_days')->default(0);
            $table->unsignedTinyInteger('retry_frequency_days')->default(0);
            $table->unsignedTinyInteger('max_retries')->default(0);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_profiles');
    }
};
