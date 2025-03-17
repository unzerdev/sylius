<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Services\Integration\PaymentPage\Processors;

use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Unzer\Core\BusinessLogic\Domain\Integration\PaymentPage\Processors\LineItemsProcessor as LineItemsProcessorInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentPage\Models\PaymentPageCreateContext;
use UnzerSDK\Constants\BasketItemTypes;
use UnzerSDK\Resources\Basket;
use UnzerSDK\Resources\EmbeddedResources\BasketItem;

/**
 * Class LineItemsProcessor
 *
 * @package SyliusUnzerPlugin\Services\Integration\PaymentPage\Processors
 */
class LineItemsProcessor implements LineItemsProcessorInterface
{
    public function __construct(private UrlGeneratorInterface $router)
    {
    }

    public function process(Basket $basket, PaymentPageCreateContext $context): void
    {
        if (!$this->shouldProcess($context)) {
            return;
        }

        /** @var OrderInterface $order */
        $order = $context->getCheckoutSession()->get('order');
        $currency = Currency::fromIsoCode($order->getCurrencyCode());

        foreach ($order->getItems() as $item) {

            $basketItem = $this->mapLineItem($item, $currency);
            $basketItemAmount = Amount::fromFloat($basketItem->getAmountPerUnitGross(), $currency);
            $basketItemDiscount = Amount::fromFloat($basketItem->getAmountDiscountPerUnitGross(), $currency);
            $basket->addBasketItem($basketItem);

            if ($item->getTotal() !== ($basketItemAmount->getValue() - $basketItemDiscount->getValue()) * $basketItem->getQuantity()) {
                $basket->addBasketItem($this->mapRoundingDiscount(
                    Amount::fromInt($item->getTotal() - $basketItemAmount->getValue() * $basketItem->getQuantity(),
                        $currency),
                    $basketItem)
                );
            }
        }

        foreach ($order->getAdjustments() as $adjustment) {
            if (
                $adjustment->getAmount() === 0 ||
                !in_array(
                    $adjustment->getType(),
                    [AdjustmentInterface::ORDER_PROMOTION_ADJUSTMENT, AdjustmentInterface::SHIPPING_ADJUSTMENT],
                    true
                )
            ) {
                continue;
            }

            if ($adjustment->getType() === AdjustmentInterface::ORDER_PROMOTION_ADJUSTMENT) {
                /** @var AdjustmentInterface $adjustment */
                $basket->addBasketItem($this->mapAdjustment($adjustment, $currency));
            }

            if ($adjustment->getType() === AdjustmentInterface::SHIPPING_ADJUSTMENT) {
                /** @var AdjustmentInterface $adjustment */
                $basket->addBasketItem($this->mapShipment($adjustment, $order, $currency));
            }
        }
    }

    /**
     * @param AdjustmentInterface $adjustment
     * @param OrderInterface $order
     * @param Currency $currency
     *
     * @return BasketItem
     */
    private function mapShipment(
        AdjustmentInterface $adjustment,
        OrderInterface $order,
        Currency $currency
    ): BasketItem {
        $amountWithTax = $order->getShippingTotal();
        $amountWithoutTax = 0;
        $adjustmentTypes = [AdjustmentInterface::SHIPPING_ADJUSTMENT, AdjustmentInterface::ORDER_SHIPPING_PROMOTION_ADJUSTMENT];

        foreach ($order->getAdjustments() as $adjustment) {
            if (in_array($adjustment->getType(), $adjustmentTypes, true)) {
                $amountWithoutTax += $adjustment->getAmount();
            }
        }

        $taxAmount = $amountWithTax - $amountWithoutTax;

        $basket = (new BasketItem())
            ->setBasketItemReferenceId((string)$adjustment->getId())
            ->setQuantity(1)
            ->setAmountPerUnitGross(
                Amount::fromInt($order->getShippingTotal(), $currency)->getPriceInCurrencyUnits()
            )
            ->setVat(100 * ($taxAmount / $amountWithoutTax))
            ->setTitle((string)$adjustment->getLabel())
            ->setType(BasketItemTypes::SHIPMENT);

        return $basket;
    }

    private function shouldProcess(PaymentPageCreateContext $context): bool
    {
        if (!$context->getCheckoutSession()->has('order')) {
            return false;
        }

        $order = $context->getCheckoutSession()->get('order');
        if (!$order instanceof OrderInterface) {
            return false;
        }

        return true;
    }

    private function mapLineItem(OrderItemInterface $item, Currency $currency): BasketItem
    {
        $amountWithoutTax = $item->getTotal() - $item->getTaxTotal();
        $taxAmount = (int)round($item->getTaxTotal() / $item->getQuantity());

        return (new BasketItem())
            ->setBasketItemReferenceId((string)$item->getId())
            ->setQuantity($item->getQuantity())
            ->setVat(100 * ($item->getTaxTotal() / $amountWithoutTax))
            ->setAmountPerUnitGross(
                Amount::fromInt($item->getUnitPrice() + $taxAmount,
                    $currency)
                    ->getPriceInCurrencyUnits()
            )

            ->setTitle((string)$item->getProductName())
            ->setSubTitle($item->getProduct()?->getShortDescription())
            //    ->setImageUrl($this->getProductImage($item))
            ->setType(BasketItemTypes::GOODS);
    }

    private function mapRoundingDiscount(Amount $amount, BasketItem $item): BasketItem
    {
        return (new BasketItem())
            ->setBasketItemReferenceId('rounding_' . (string)$item->getBasketItemReferenceId())
            ->setQuantity(1)
            ->setAmountDiscountPerUnitGross(abs($amount->getPriceInCurrencyUnits()))
            ->setTitle('Rounding discount')
            ->setType(BasketItemTypes::VOUCHER);
    }

    private function mapAdjustment(AdjustmentInterface $adjustment, Currency $currency): BasketItem
    {
        $isShipment = $adjustment->getType() === AdjustmentInterface::SHIPPING_ADJUSTMENT;
        $type = $isShipment ? BasketItemTypes::SHIPMENT : BasketItemTypes::VOUCHER;
        if (!$isShipment && $adjustment->getAmount() > 0) {
            $type = BasketItemTypes::GOODS;
        }

        return (new BasketItem())
            ->setBasketItemReferenceId((string)$adjustment->getId())
            ->setQuantity(1)
            ->setAmountPerUnitGross(
                Amount::fromInt(abs($adjustment->getAmount()), $currency)->getPriceInCurrencyUnits()
            )
            ->setTitle((string)$adjustment->getLabel())
            ->setType($type);
    }

    private function getUnitPriceWithTax(OrderItemInterface $item): int
    {
        $totalTax = $item->getTaxTotal();

        $fullUnitPrice = $item->getFullDiscountedUnitPrice();
        if (0 >= $totalTax) {
            return $fullUnitPrice;
        }

        if ($fullUnitPrice * $item->getQuantity() < $item->getTotal()) {
            return (int)round($fullUnitPrice + ($item->getTaxTotal() / $item->getQuantity()));
        }

        return $fullUnitPrice;
    }

    private function getProductImage(OrderItemInterface $item): ?string
    {
        $image = $item->getProduct()?->getImages()->first();
        if ($image === false || $image === null) {
            return null;
        }

        return $this->router->generate(
            'liip_imagine_filter',
            ['filter' => 'sylius_admin_product_original', 'path' => $image->getPath()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
