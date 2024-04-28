<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Enums\Concerns;

use Random\RandomException;

trait HasRandom
{
    /**
     * @throws RandomException
     */
    public static function random(): self
    {
        return static::cases()[random_int(0, count(static::cases()) - 1)];
    }

    /**
     * @throws RandomException
     */
    public static function randomValue(): string
    {
        return static::random()->value;
    }
}