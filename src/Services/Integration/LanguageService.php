<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Services\Integration;

use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Locale\Model\LocaleInterface;
use Unzer\Core\BusinessLogic\Domain\Integration\Language\LanguageService as CoreLanguageService;
use Unzer\Core\BusinessLogic\Domain\Language\Models\Language;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;

/**
 * Class LanguageService.
 *
 * @package SyliusUnzerPlugin\Services\Integration
 */
class LanguageService implements CoreLanguageService
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
     * Maps Sylius front office languages to Unzer core Language model.
     *
     * @return Language[]
     */
    public function getLanguages(): array
    {
        $channelId = StoreContext::getInstance()->getStoreId();

        /** @var ChannelInterface $channel */
        $channel = $this->channelRepository->find($channelId);

        if ($channel == null) {
            return [];
        }

        return $channel->getLocales()->map(function (LocaleInterface $locale) {
            $localeCode = $locale->getCode();
            $flag = is_string($localeCode) ? $this->getFlagFromCode($localeCode) : 'default';

            return new Language($localeCode ?? 'unknown', $flag);
        })->toArray();
    }

    /**
     * Extracts the country code from the locale code (e.g. 'en_US' -> 'us').
     *
     * @param string $localeCode
     *
     * @return string
     */
    private function getFlagFromCode(string $localeCode): string
    {
        $parts = explode('_', $localeCode);
        if (!isset($parts[1])) {
            return 'default';
        }

        $countryCode = strtolower($parts[1]);
        return 'country-' . $countryCode;
    }
}
