<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Models;

use Appleton\Subscriptions\Enums\TimePeriod;
use Carbon\Carbon;
use Database\Factories\SubscriptionProfileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * @property-read int $id
 * @property string $uuid
 * @property int|null $user_id
 *
 * @property string|null $name
 * @property string|null $description
 *
 * @property string|null $currency
 * @property float|null $amount
 *
 * @property TimePeriod $subscription_period
 * @property int $subscription_period_multiplier
 *
 * @property TimePeriod $payment_period
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
 * @property Carbon $deleted_at
 * @property Carbon $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Model|null $user
 */
class SubscriptionProfile extends Model
{
    use SoftDeletes;
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'user_id',
        'name',
        'description',
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
    ];

    public function getTable(): string
    {
        return config()->string('subscriptions.table_names.subscription_profiles', parent::getTable());
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'uuid' => 'string',
        'name' => 'array',
        'description' => 'array',
        'amount' => 'float',
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
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array<string>
     */
    protected array $dates = [
        'deleted_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the user that owns the subscription profile.
     *
     * @return BelongsTo<Model, SubscriptionProfile>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config()->classString('subscriptions.user_model'));
    }

    protected static function newFactory(): SubscriptionProfileFactory
    {
        return SubscriptionProfileFactory::new();
    }

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (SubscriptionProfile $profile) {
            $profile->uuid ??= (string) Str::uuid();
        });
    }
}
