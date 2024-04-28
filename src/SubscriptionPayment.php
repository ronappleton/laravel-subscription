<?php

declare(strict_types=1);

namespace Appleton\Subscriptions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubscriptionPayment extends Model
{
    use SoftDeletes;
}
