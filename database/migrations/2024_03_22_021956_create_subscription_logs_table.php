<?php

declare(strict_types=1);

use Appleton\Subscriptions\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subscription_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->unsignedBigInteger('subscription_id');
            $table->decimal('amount', 18, 10);
            $table->enum('status', PaymentStatus::values())->default('unpaid');
            $table->timestamp('created_at');
            $table->softDeletes();

            $table->foreign('subscription_id')
                ->references('id')
                ->on('subscriptions')
                ->restrictOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_logs');
    }
};
