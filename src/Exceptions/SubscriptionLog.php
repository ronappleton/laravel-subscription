<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Exceptions;

use Appleton\Subscriptions\Enums\PaymentStatus;
use Exception;

class SubscriptionLog extends Exception
{
    public static function InvalidStatus(PaymentStatus $status): self
    {
        throw new self("Invalid Subscription Log status: {$status->value}");
    }
}