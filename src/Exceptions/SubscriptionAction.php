<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Exceptions;

use Exception;
use Appleton\Subscriptions\Contracts\SubscriptionAction as SubscriptionActionContract;

class SubscriptionAction extends Exception
{
    /**
     * @throws SubscriptionAction
     */
    public static function notFound(string $action): self
    {
        throw new self("Action {$action} not found");
    }

    /**
     * @throws SubscriptionAction
     */
    public static function notAnAction(string $action): self
    {
        throw new self(sprintf('Action %s must implement %s', $action, SubscriptionActionContract::class));
    }
}