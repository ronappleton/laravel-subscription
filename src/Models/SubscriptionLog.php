<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Models;

use Appleton\Subscriptions\Enums\PaymentStatus;
use Appleton\Subscriptions\Observers\SubscriptionLogObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property-read int $id
 * @property string $uuid
 * @property int $subscription_id
 * @property float $amount
 * @property PaymentStatus $status
 * @property Carbon $created_at
 * @property Carbon|null $deleted_at
 * @property Subscription $subscription
 * @method static Builder ageInDays(int $days)
 * @method static Builder status(PaymentStatus $status)
 */
#[ObservedBy(SubscriptionLogObserver::class)]
class SubscriptionLog extends Model
{
    use SoftDeletes;

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'subscription_id',
        'amount',
        'status',
        'created_at',
    ];

    public function getTable(): string
    {
        return config()->string('subscriptions.table_names.subscription_logs', parent::getTable());
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'amount' => 'double',
            'status' => PaymentStatus::class,
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Subscription, SubscriptionLog>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * @param Builder<SubscriptionLog> $query
     * @return Builder<SubscriptionLog>
     */
    public function scopeAgeInDays(Builder$query, int $days): Builder
    {
        return $query->where('created_at', '<=', now()->subDays($days));
    }

    /**
     * @param Builder<SubscriptionLog> $query
     *
     * @return Builder<SubscriptionLog>
     */
    public function scopeStatus(Builder $query, PaymentStatus $status): Builder
    {
        return $query->where('status', $status->value);
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (SubscriptionLog $log): void {
            $log->uuid ??= (string) Str::uuid();
        });
    }
}
