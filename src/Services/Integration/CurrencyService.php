<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Services\Integration;

use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Currency\Converter\CurrencyConverterInterface;
use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\InvalidCurrencyCode;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Unzer\Core\BusinessLogic\Domain\Integration\Currency\CurrencyServiceInterface;
use Unzer\Core\BusinessLogic\Domain\Multistore\StoreContext;

/**
 * Class CurrencyService.
 *
 * @package SyliusUnzerPlugin\Services\Integration
 */
class CurrencyService implements CurrencyServiceInterface
{
    /**
     * @var ChannelRepositoryInterface<ChannelInterface> $channelRepository
     */
    private ChannelRepositoryInterface $channelRepository;

    /**
     * @var CurrencyConverterInterface
     */
    private CurrencyConverterInterface $currencyConverter;

    /**
     * @param ChannelRepositoryInterface<ChannelInterface> $channelRepository
     * @param CurrencyConverterInterface $currencyConverter
     */
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        CurrencyConverterInterface $currencyConverter
    ) {
        $this->channelRepository = $channelRepository;
        $this->currencyConverter = $currencyConverter;
    }

    /**
     * @return Currency
     */
    public function getDefaultCurrency(): Currency
    {
        $channelId = StoreContext::getInstance()->getStoreId();

        /** @var ?ChannelInterface $channel */
        $channel = $this->channelRepository->find($channelId);

        if ($channel === null) {
            return Currency::getDefault();
        }

        $baseCurrency = $channel->getBaseCurrency();
        if ($baseCurrency === null) {
            return Currency::getDefault();
        }

        try {
            return Currency::fromIsoCode($baseCurrency->getCode());
        } catch (InvalidCurrencyCode) {
            return Currency::getDefault();
        }
    }

    /**
     * @param Amount $baseAmount
     * @param Currency $targetCurrency
     *
     * @return Amount
     */
    public function convert(Amount $baseAmount, Currency $targetCurrency): Amount
    {
        $convertedAmount = $this->currencyConverter->convert(
            $baseAmount->getValue(),
            $baseAmount->getCurrency()->getIsoCode(),
            $targetCurrency->getIsoCode()
        );

        return Amount::fromInt($convertedAmount, $targetCurrency);
    }
}
