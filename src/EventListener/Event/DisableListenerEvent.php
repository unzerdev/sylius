<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\EventListener\Event;

final class DisableListenerEvent
{
    public const WEBHOOKS = 'unzer.sylius.disable_webhook_listener';
}
