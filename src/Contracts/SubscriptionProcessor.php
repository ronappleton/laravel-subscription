<?php

declare(strict_types=1);

namespace Appleton\Subscriptions\Contracts;

interface SubscriptionProcessor
{
    public function process(): void;
    public function processWarnings(): void;
    public function processRetries(): void;
}