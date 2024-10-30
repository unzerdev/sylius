<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Services\Integration;

use Sylius\Component\Addressing\Model\CountryInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Unzer\Core\BusinessLogic\Domain\Country\Models\Country;
use Unzer\Core\BusinessLogic\Domain\Integration\Country\CountryService as CountryServiceInterface;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;

/**
 * Class CountryService.
 *
 * @package SyliusUnzerPlugin\Services\Integration
 */
class CountryService implements CountryServiceInterface
{
    /**
     * @var ChannelRepositoryInterface<ChannelInterface> $channelRepository
     */
    private ChannelRepositoryInterface $channelRepository;

    /**
     * @param ChannelRepositoryInterface<ChannelInterface> $channelRepository
     */
    public function __construct(ChannelRepositoryInterface $channelRepository)
    {
        $this->channelRepository = $channelRepository;
    }

    /**
     * @return Country[]
     */
    public function getCountries(): array
    {
        $channelId = StoreContext::getInstance()->getStoreId();

        /** @var ChannelInterface $channel */
        $channel = $this->channelRepository->find($channelId);

        if ($channel == null) {
            return [];
        }

        return $channel->getCountries()->map(function (CountryInterface $country) {
            /** @var string $code */
            $code = $country->getCode();

            /** @var string $name */
            $name = $country->getName();

            return new Country($code, $name);
        })->toArray();
    }
}
