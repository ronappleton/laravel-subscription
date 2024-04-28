<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Exceptions;

use Exception;

class Subscription extends Exception
{
    public static function InvalidStatus(string $status): void
    {
        throw new self("Invalid Subscription status: {$status}");
    }
}