<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Models\Concerns;

use Appleton\Subscriptions\Enums\Status;
use Appleton\Subscriptions\Models\Subscription;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin Subscription
 *
 * @property Status $status
 * @method static Builder status(Status $status)
 */
trait HasStatus
{
    use HasActivate;
    use HasPause;
    use HasResume;
    use HasCancel;
    use HasSuspend;
    use HasEnd;

    /**
     * @param Builder<Subscription> $query
     *
     * @return Builder<Subscription>
     */
    public function scopeStatus(Builder $query, Status $status): Builder
    {
        return $query->where('status', $status->value);
    }
}