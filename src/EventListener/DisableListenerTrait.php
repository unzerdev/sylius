<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\EventListener;

trait DisableListenerTrait
{
    private bool $enabled = true;

    public function disable(): void
    {
        $this->enabled = false;
    }
}
