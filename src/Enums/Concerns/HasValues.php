<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Enums\Concerns;

trait HasValues
{
    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}