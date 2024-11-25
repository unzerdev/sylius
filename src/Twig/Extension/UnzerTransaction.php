<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Twig\Extension;

use Sylius\Component\Core\Model\OrderInterface;
use SyliusUnzerPlugin\Util\StaticHelper;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;
use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\AdminAPI\Transaction\Request\GetTransactionHistoryRequest;
use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\CurrencyMismatchException;
use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\InvalidCurrencyCode;

/**
 * Class UnzerTransaction.
 *
 * @package SyliusUnzerPlugin\Twig\Extension
 */
class UnzerTransaction extends AbstractExtension
{
    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'unzer_transaction',
                [$this, 'getUnzerTransaction'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * @param OrderInterface $order
     *
     * @return array
     *
     * @throws CurrencyMismatchException
     * @throws InvalidCurrencyCode
     */
    public function getUnzerTransaction(OrderInterface $order): array
    {
        $payment = $order->getLastPayment();
        if ($payment?->getMethod()?->getCode() !== StaticHelper::UNZER_PAYMENT_METHOD_GATEWAY) {
            return [];
        }

        $channelId = $order->getChannel() !== null ? $order->getChannel()->getId() : '';
        $orderId = (string)$order->getId();

        $transactionResponse = AdminAPI::get()->transaction($channelId)->getTransactionHistory(new GetTransactionHistoryRequest($orderId));
        if (!$transactionResponse->isSuccessful()) {
            return [];
        }

        return $transactionResponse->toArray();
    }
}
