<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Exceptions;

use Exception;

class SubscriptionAction extends Exception
{
    public static function notFound(string $action): self
    {
        return new self("Action {$action} not found");
    }

    public static function couldNotBeBuilt(string $action, string $message): self
    {
        return new self("Action {$action} could not be built [$message]");
    }

    public static function notAnAction(string $action): self
    {
        return new self("{$action} is not an action");
    }
}