<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Enums\Contracts;

interface Values
{
    /**
     * @return array<int, string>
     */
    public static function values(): array;
}