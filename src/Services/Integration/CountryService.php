<?php

namespace SyliusUnzerPlugin\Services\Integration;

use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Unzer\Core\BusinessLogic\Domain\Country\Models\Country;
use Unzer\Core\BusinessLogic\Domain\Integration\Country\CountryService as CountryServiceInterface;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;
use Sylius\Component\Addressing\Model\Country as SyliusCountry;

/**
 * Class CountryService.
 *
 * @package SyliusUnzerPlugin\Services\Integration
 */
class CountryService implements CountryServiceInterface
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
     * @return Country[]
     */
    public function getCountries(): array
    {
        $channelId = StoreContext::getInstance()->getStoreId();
        $channel = $this->channelRepository->find($channelId);

        if ($channel == null) {
            return [];
        }

        return $channel->getCountries()->map(function (SyliusCountry $country) {
            return new Country($country->getCode(), $country->getName());
        })->toArray();
    }
}
