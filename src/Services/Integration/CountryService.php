<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Services\Integration;

use Sylius\Component\Addressing\Model\CountryInterface;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
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

    /** @var RepositoryInterface $countryRepository */
    private RepositoryInterface $countryRepository;

    /**
     * @param ChannelRepositoryInterface<ChannelInterface> $channelRepository
     * @param RepositoryInterface $countryRepository
     */
    public function __construct(ChannelRepositoryInterface $channelRepository, RepositoryInterface $countryRepository)
    {
        $this->channelRepository = $channelRepository;
        $this->countryRepository = $countryRepository;
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

        $channelCountries = $channel->getCountries();

        if ($channelCountries->count() === 0) {
            return $this->getShopEnabledCountries();
        }

        return $channelCountries->map(function (CountryInterface $country) {
            /** @var string $code */
            $code = $country->getCode();
            /** @var string $name */
            $name = $country->getName();

            return new Country($code, $name);
        })->toArray();
    }

    /**
     * @return array
     */
    private function getShopEnabledCountries(): array
    {
        /** @var CountryInterface[] $syliusCountries */
        $syliusCountries = $this->countryRepository->findBy(['enabled' => true]);
        $countries = [];

        foreach ($syliusCountries as $country) {
            /** @var string $code */
            $code = $country->getCode();
            /** @var string $name */
            $name = $country->getName();

            $countries[] = new Country($code, $name);
        }

        return $countries;
    }
}
