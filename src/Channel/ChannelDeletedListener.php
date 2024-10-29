<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Channel;

use Sylius\Component\Core\Model\ChannelInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use UnzerSDK\Exceptions\UnzerApiException;

/**
 * Class ChannelDeletedListener.
 *
 * @package SyliusUnzerPlugin\Channel
 */
class ChannelDeletedListener
{
    /**
     * @throws UnzerApiException
     */
    public function onChannelDelete(GenericEvent $event): void
    {
        $channel = $event->getSubject();

        if (!$channel instanceof ChannelInterface) {
            return;
        }

        $channelId = (string)$channel->getId();
        AdminAPI::get()->disconnect($channelId)->disconnect();
    }
}
