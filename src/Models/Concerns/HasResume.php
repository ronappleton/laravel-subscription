<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Models\Concerns;

use Appleton\Subscriptions\Enums\Status;
use Appleton\Subscriptions\Models\Subscription;
use Carbon\Carbon;
use Closure;

/**
 * @mixin Subscription
 * @mixin HasStatus
 *
 * @property ?Carbon $resumed_at
 */
trait HasResume
{
    private array $resumableStatuses = [
        Status::PAUSED,
        Status::SUSPENDED,
    ];

    public function resume(): void
    {
        if (!$this->canResume()) {
            return;
        }

        $this->paused_at = null;
        $this->resumed_at = Carbon::now();
        $this->status = Status::ACTIVE;
        $this->save();
    }

    public function isResumed(): bool
    {
        return $this->status === Status::ACTIVE;
    }

    /**
     * A custom callback to determine if the subscription can be resumed for example a role or permission check.
     */
    public function canResume(?Closure $callback = null): bool
    {
        $baseChecks = !is_null($this->activated_at)
            && !is_null($this->paused_at) || !is_null($this->suspended_at)
            && is_null($this->resumed_at)
            && in_array($this->status, $this->resumableStatuses, true);

        if (is_callable($callback)) {
            return $baseChecks && $callback($this);
        }

        return $baseChecks;
    }
}