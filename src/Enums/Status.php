<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Enums;

use Appleton\Subscriptions\Enums\Concerns\HasRandom;
use Appleton\Subscriptions\Enums\Concerns\HasValues;
use Appleton\Subscriptions\Enums\Contracts\Values;

enum Status: string implements Values
{
    use HasValues;
    use HasRandom;

    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case CANCELLED = 'cancelled';
    case ENDED = 'ended';
    case SUSPENDED = 'suspended';
    case PAUSED = 'paused';
}
