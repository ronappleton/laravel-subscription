<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Exceptions;

use Exception;

class SubscriptionLog extends Exception
{
    public static function InvalidStatus(string $status): self
    {
        throw new self("Invalid Subscription Log status: {$status}");
    }
}