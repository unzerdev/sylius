<?php

namespace SyliusUnzerPlugin\Services\Integration;
use ReflectionClass;
use Sylius\Component\Channel\Model\Channel;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\OrderPaymentStates;
use Symfony\Contracts\Translation\TranslatorInterface;
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
    private TranslatorInterface $translator;

    /**
     * @param ChannelRepositoryInterface $channelRepository
     * @param TranslatorInterface $translator
     */
    public function __construct(ChannelRepositoryInterface $channelRepository, TranslatorInterface $translator)
    {
        $this->channelRepository = $channelRepository;
        $this->translator = $translator;
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
     * @param int $id
     *
     * @return Store|null
     */
    public function getStoreById(int $id): ?Store
    {
        /** @var ?Channel $channel */
        $channel = $this->channelRepository->findOneBy(['id' => $id]);

        if (!$channel) {
            return null;
        }

        return new Store($channel->getId(), $channel->getName());
    }

    /**
     * @return StoreOrderStatus[]
     */
    public function getStoreOrderStatuses(): array
    {
        $reflection = new ReflectionClass(OrderPaymentStates::class);
        $values = array_values($reflection->getConstants());

        $orderStatuses = [];
        foreach ($values as $value) {
            $orderStatuses[] = new StoreOrderStatus($value, $this->translator->trans("sylius.ui.$value"));
        }

        return $orderStatuses;
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
