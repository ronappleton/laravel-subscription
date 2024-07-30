<?php

declare(strict_types=1);

use Appleton\Subscriptions\Enums\Status;
use Appleton\Subscriptions\Enums\TimePeriod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create(config()->string('subscriptions.table_names.subscriptions'), function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('action_class');
            $table->foreignUuid('payer_id')->constrained('users');
            $table->foreignUuid('payee_id')->constrained('users');
            $table->string('currency');
            $table->decimal('amount', 18, 10);

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
            ])->nullable();
            $table->boolean('allow_fixed_day_change')->default(false);

            $table->boolean('allow_pause')->default(false);
            $table->boolean('allow_cancel')->default(false);

            // Manage warnings and retries
            $table->unsignedBigInteger('advanced_warning_days')->default(0);
            $table->unsignedBigInteger('retry_frequency_days')->default(0);
            $table->unsignedBigInteger('max_retries')->default(0);

            $table->enum('status', Status::values());

            $table->timestamp('activated_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('resumed_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
