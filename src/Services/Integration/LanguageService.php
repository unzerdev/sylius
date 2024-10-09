<?php

namespace SyliusUnzerPlugin\Services\Integration;

use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Locale\Model\Locale;
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
     * Maps Sylius front office languages to Unzer core Language model.
     *
     * @return Language[]
     */
    public function getLanguages(): array
    {
        $channelId = StoreContext::getInstance()->getStoreId();
        $channel = $this->channelRepository->find($channelId);

        if ($channel == null) {
            return [];
        }

        return $channel->getLocales()->map(function (Locale $locale) {
            return new Language($locale->getCode());
        })->toArray();
    }
}
