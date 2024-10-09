<?php

namespace SyliusUnzerPlugin\Services\Integration;

use App\Entity\Channel\Channel;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\Stores\Models\Store;
use Unzer\Core\BusinessLogic\Domain\Integration\Store\StoreService as StoreServiceInterface;
use Unzer\Core\BusinessLogic\Domain\Stores\Models\StoreOrderStatus;

/**
 * Class StoreService.
 *
 * @package SyliusUnzerPlugin\Services\Integration
 */
class StoreService implements StoreServiceInterface
{
    /**
     * @var ChannelRepositoryInterface
     */
    private ChannelRepositoryInterface $channelRepository;

    /**
     * @param ChannelRepositoryInterface $channelRepository
     */
    public function __construct(ChannelRepositoryInterface $channelRepository)
    {
        $this->channelRepository = $channelRepository;
    }

    /**
     * @return Store[]
     */
    public function getStores(): array
    {
        /** @var Channel[] $channels */
        $channels = $this->channelRepository->findAll();

        return array_map(function (Channel $channel) {
            return new Store(
                $channel->getId(),
                $channel->getName()
            );
        }, $channels);
    }

    /**
     * @return ?Store
     */
    public function getDefaultStore(): ?Store
    {
        return $this->getFirstEnabledStore();
    }

    /**
     * @return ?Store
     */
    public function getCurrentStore(): ?Store
    {
        return $this->getFirstEnabledStore();
    }

    /**
     * @return StoreOrderStatus[]
     */
    public function getStoreOrderStatuses(): array
    {
        return [];
    }

    /**
     * @return ?Store
     */
    private function getFirstEnabledStore(): ?Store
    {
        /** @var ?Channel $channel */
        $channel = $this->channelRepository->findOneBy(['enabled' => true]);

        if (!$channel) {
            return null;
        }

        return new Store($channel->getId(), $channel->getName());
    }
}
