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
 * @property ?Carbon $paused_at
 */
trait HasPause
{
    private array $pauseableStatuses = [
        Status::ACTIVE,
    ];

    public function pause(): void
    {
        if (!$this->canPause()) {
            return;
        }

        $this->paused_at = Carbon::now();
        $this->status = Status::PAUSED;
        $this->save();
    }

    public function isPaused(): bool
    {
        return $this->paused_at !== null;
    }

    /**
     * A custom callback to determine if the subscription can be paused for example a role or permission check.
     */
    public function canPause(?Closure $callback = null): bool
    {
        $baseChecks = !is_null($this->activated_at)
            && is_null($this->paused_at)
            && is_null($this->ended_at)
            && $this->allow_pause
            && in_array($this->status, $this->pauseableStatuses, true);

        if (is_callable($callback)) {
            return $baseChecks && $callback($this);
        }

        return $baseChecks;
    }
}