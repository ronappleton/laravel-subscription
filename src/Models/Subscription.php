<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Models;

use Appleton\Subscriptions\Contracts\SubscriptionAction;
use Appleton\Subscriptions\Enums\PaymentStatus;
use Appleton\Subscriptions\Enums\TimePeriod;
use Appleton\Subscriptions\Enums\Status;
use Appleton\Subscriptions\Exceptions\SubscriptionAction as SubscriptionActionException;
use Appleton\Subscriptions\Observers\SubscriptionObserver;
use Carbon\Carbon;
use Database\Factories\SubscriptionFactory;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\DB;

/**
 * @property-read int $id
 * @property string $uuid
 * @property string $action_class
 * @property string $payer_type
 * @property int $payer_id
 * @property string $payee_type
 * @property int $payee_id
 * @property string $currency
 * @property float $amount
 *
 * @property string $subscription_period
 * @property int $subscription_period_multiplier
 *
 * @property string $payment_period
 * @property int $payment_frequency_multiplier
 *
 * @property int $fixed_day_of_month
 * @property bool $allow_fixed_day_change
 *
 * @property bool $allow_pause
 * @property bool $allow_cancel
 *
 * @property int $advanced_warning_days
 * @property int $retry_frequency_days
 * @property int $max_retries
 *
 * @property Status $status
 *
 * @property Carbon|null $deleted_at
 * @property Carbon|null $paused_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property Model $payer
 * @property Model $payee
 * @property SubscriptionLog[] $logs
 * @method static Builder forDay()
 * @method static Builder status(Status $status)
 * @method static Builder forWarning()
 * @method static Builder forRetry()
 */
#[ObservedBy(SubscriptionObserver::class)]
class Subscription extends Model
{
    use SoftDeletes;
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'action_class',
        'payer_type',
        'payer_id',
        'payee_type',
        'payee_id',
        'currency',
        'amount',

        'subscription_period',
        'subscription_period_multiplier',

        'payment_period',
        'payment_frequency_multiplier',

        'fixed_day_of_month',
        'allow_fixed_day_change',

        'allow_pause',
        'allow_cancel',

        'advanced_warning_days',
        'retry_frequency_days',
        'max_retries',

        'status',

        'paused_at',
    ];


    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'amount' => 'double',
            'subscription_period' => TimePeriod::class,
            'subscription_period_multiplier' => 'int',
            'payment_period' => TimePeriod::class,
            'payment_frequency_multiplier' => 'int',
            'fixed_day_of_month' => 'int',
            'allow_fixed_day_change' => 'bool',
            'allow_pause' => 'bool',
            'allow_cancel' => 'bool',
            'advanced_warning_days' => 'int',
            'retry_frequency_days' => 'int',
            'max_retries' => 'int',
            'status' => Status::class,
            'paused_at' => 'datetime',
        ];
    }

    protected static function newFactory(): SubscriptionFactory
    {
        return SubscriptionFactory::new();
    }

    public function getAction(): SubscriptionAction
    {
        if (!class_exists($this->action_class)) {
            SubscriptionActionException::notFound($this->action_class);
        }

        $actionClass = null;

        try {
            $actionClass = app()->make($this->action_class);
        } catch (BindingResolutionException $e) {
            SubscriptionActionException::couldNotBeBuilt($this->action_class, $e->getMessage());
        }

        if (!$actionClass instanceof SubscriptionAction) {
            SubscriptionActionException::notAnAction($this->action_class);
        }

        return $actionClass;
    }

    /**
     * @return MorphTo<Model, Subscription>
     */
    public function payer(): MorphTo
    {
        return $this->morphTo('payer');
    }

    /**
     * @return MorphTo<Model, Subscription>
     */
    public function payee(): MorphTo
    {
        return $this->morphTo('payee');
    }

    /**
     * @return HasMany<SubscriptionLog>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(SubscriptionLog::class);
    }

    /**
     * @param Builder<Subscription> $query
     *
     * @return Builder<Subscription>
     */
    public function scopeForDay(Builder $query): Builder
    {
        return $query->where('fixed_day_of_month', Carbon::now()->day);
    }

    /**
     * @param Builder<Subscription> $query
     *
     * @return Builder<Subscription>
     */
    public function scopeStatus(Builder $query, Status $status): Builder
    {
        return $query->where('status', $status->value);
    }

    /**
     * @param Builder<Subscription> $query
     *
     * @return Builder<Subscription>
     */
    public function scopeForWarning(Builder $query): Builder
    {
        $day = Carbon::now()->day;

        return $query
            ->where('status', '=', Status::ACTIVE->value) // Must be active
            ->where('advanced_warning_days', '>', 0) // Advanced Warnings must be enabled (>0)
            ->whereRaw("advanced_warning_days + $day = fixed_day_of_month"); // Must be the day of the month
    }

//    /**
//     * @param Builder<SubscriptionLog> $query
//     *
//     * @return Builder<SubscriptionLog>
//     */
//    public function scopeUnpaidLogsThisMonth(Builder $query): Builder
//    {
//        return $query->where('status', PaymentStatus::UNPAID->value)
//            ->where(function ($query) {
//                $query->whereMonth('created_at', '=', Carbon::now()->month)
//                    ->orWhere(function ($query) {
//                        $query->whereMonth('created_at', '=', Carbon::now()->subMonth()->month)
//                            ->whereRaw("strftime('%d', created_at) >= strftime('%d', date('now', '-' || fixed_day_of_month || ' days'))");
//                    });
//            });
//    }

    /**
     * @param Builder<Subscription> $query
     *
     * @return Builder<Subscription>
     */
    public function scopeFailedPaymentsLastMonth(Builder $query): Builder
    {
        return $query->whereHas('logs', function ($query) {
            $query->where('status', PaymentStatus::UNPAID->value)
                ->whereBetween('created_at', [Carbon::now()->subMonth(), Carbon::now()]);
        })->whereDoesntHave('logs', function ($query) {
            $query->where('status', PaymentStatus::PAID->value)
                ->where('created_at', '>', Carbon::now()->subMonth());
        });
    }

    /**
     * @param Builder<Subscription> $query
     *
     * @return Builder<Subscription>
     */
    public function scopeUnpaidLogsNotEqualToMaxRetries(Builder $query): Builder
    {
        return $query->whereHas('logs', function ($query) {
            $query->select(DB::raw('subscription_id, count(*) as unpaid_count'))
                ->where('status', PaymentStatus::UNPAID->value)
                ->groupBy('subscription_id')
                ->havingRaw('count(*) < max_retries');
        });
    }

    /**
     * @param Builder<Subscription> $query
     *
     * @return Builder<Subscription>
     */
    public function scopeRetryFrequencyDaysSinceLastUnpaidLog(Builder $query): Builder
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        return $query->whereHas('logs', function ($query) use ($now) {
            $query->where('status', PaymentStatus::UNPAID->value)
                ->whereRaw("created_at = datetime('" . $now . "', '-' || retry_frequency_days || ' days')")
                ->latest();
        });
    }

    /**
     * @param Builder<Subscription> $query
     *
     * @return Builder<Subscription>
     */
    public function scopeForRetry(Builder $query): Builder
    {
        return $query->where('status', Status::ACTIVE->value)
            ->where('max_retries', '>', 0)
            ->failedPaymentsLastMonth()
            ->unpaidLogsNotEqualToMaxRetries()
            ->retryFrequencyDaysSinceLastUnpaidLog();
    }

    public function canPause(): bool
    {
        return $this->allow_pause;
    }

    public function pause(): void
    {
        $this->paused_at = now();
        $this->save();
    }

    public function isPaused(): bool
    {
        return $this->paused_at !== null;
    }

    public function resume(): void
    {
        $this->paused_at = null;
        $this->save();
    }

    public function canCancel(): bool
    {
        return $this->allow_cancel;
    }

    public function cancel(): void
    {
        $this->status = Status::CANCELLED;
        $this->save();
    }

    public function isCancelled(): bool
    {
        return $this->status === Status::CANCELLED;
    }

    public function end(): void
    {
        $this->status = Status::ENDED;
        $this->save();
    }

    public function isEnded(): bool
    {
        return $this->status === Status::ENDED;
    }

    public function suspend(): void
    {
        $this->status = Status::SUSPENDED;
        $this->save();
    }

    public function isSuspended(): bool
    {
        return $this->status === Status::SUSPENDED;
    }

    public function activate(): void
    {
        $this->status = Status::ACTIVE;
        $this->save();
    }

    public function isActive(): bool
    {
        return $this->status === Status::ACTIVE;
    }

    public function isDue(): bool
    {
        return $this->fixed_day_of_month === now()->day;
    }

    public function isFixedDayChangeAllowed(): bool
    {
        return $this->allow_fixed_day_change;
    }
}
