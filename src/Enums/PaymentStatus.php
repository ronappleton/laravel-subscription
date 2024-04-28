<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Enums;

use Appleton\Subscriptions\Enums\Concerns\HasRandom;
use Appleton\Subscriptions\Enums\Concerns\HasValues;
use Appleton\Subscriptions\Enums\Contracts\Values;

enum PaymentStatus: string implements Values
{
    use HasValues;
    use HasRandom;

    case PAID = 'paid';
    case UNPAID = 'unpaid';
}
