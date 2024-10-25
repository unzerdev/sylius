<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\EventListener;

interface DisableListenerInterface
{
    public function disable(): void;
}
