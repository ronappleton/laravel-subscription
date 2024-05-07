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
 * @property ?Carbon $ended_at
 */
trait HasEnd
{
    private array $endableStatuses = [
        Status::ACTIVE,
        Status::PAUSED,
        Status::SUSPENDED,
    ];

    public function end(): void
    {
        if (!$this->canEnd()) {
            return;
        }

        $this->ended_at = Carbon::now();
        $this->status = Status::ENDED;
        $this->save();
    }

    public function isEnded(): bool
    {
        return $this->status === Status::ENDED;
    }

    /**
     * A custom callback to determine if the subscription can be ended for example a role or permission check.
     */
    public function canEnd(?Closure $callback = null): bool
    {
        $baseChecks = !is_null($this->activated_at)
            && is_null($this->ended_at)
            && in_array($this->status, $this->endableStatuses, true);

        if (is_callable($callback)) {
            return $baseChecks && $callback($this);
        }

        return $baseChecks;
    }
}