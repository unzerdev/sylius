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
            $basket->addBasketItem($this->mapLLineItem($item, $currency));
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
        foreach ($order->getAdjustments(AdjustmentInterface::SHIPPING_ADJUSTMENT) as $shippingAdjustment) {
            $amountWithoutTax += $shippingAdjustment->getAmount();
        }
        $taxAmount = $amountWithTax - $amountWithoutTax;

        return (new BasketItem())
            ->setBasketItemReferenceId((string)$adjustment->getId())
            ->setQuantity(1)
            ->setAmountPerUnitGross(
                Amount::fromInt($order->getShippingTotal(), $currency)->getPriceInCurrencyUnits()
            )
            ->setAmountPerUnit(
                Amount::fromInt($amountWithTax, $currency)->getPriceInCurrencyUnits()
            )
            ->setAmountGross(
                Amount::fromInt($amountWithTax, $currency)->getPriceInCurrencyUnits()
            )
            ->setAmountNet(
                Amount::fromInt($amountWithoutTax, $currency)->getPriceInCurrencyUnits()
            )
            ->setAmountVat(
                Amount::fromInt($amountWithTax - $amountWithoutTax, $currency)->getPriceInCurrencyUnits()
            )
            ->setVat(100 * ($taxAmount / $amountWithoutTax))
            ->setTitle((string)$adjustment->getLabel())
            ->setType(BasketItemTypes::SHIPMENT);
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

    private function mapLLineItem(OrderItemInterface $item, Currency $currency): BasketItem
    {
        $amountWithTax = $this->getUnitPriceWithTax($item);
        $taxAmount = $item->getTaxTotal();
        $amountWithoutTax = $amountWithTax - (int)round($taxAmount / $item->getQuantity());

        return (new BasketItem())
            ->setBasketItemReferenceId((string)$item->getId())
            ->setQuantity($item->getQuantity())
            ->setVat((100 * ($taxAmount / $amountWithoutTax)) / $item->getQuantity())
            ->setAmountDiscountPerUnitGross(
                Amount::fromInt($item->getUnitPrice() - $item->getFullDiscountedUnitPrice(), $currency)
                    ->getPriceInCurrencyUnits()
            )
            ->setAmountPerUnitGross(
                Amount::fromInt($amountWithTax, $currency)->getPriceInCurrencyUnits()
            )
            ->setAmountDiscount(
                Amount::fromInt($item->getUnitPrice() - $item->getFullDiscountedUnitPrice(), $currency)
                    ->getPriceInCurrencyUnits()
            )
            ->setAmountPerUnit(
                Amount::fromInt($item->getUnitPrice() + (int)round($item->getTaxTotal() / $item->getQuantity()),
                    $currency)->getPriceInCurrencyUnits()
            )
            ->setAmountGross(
                Amount::fromInt($item->getTotal(), $currency)->getPriceInCurrencyUnits()
            )
            ->setAmountNet(
                Amount::fromInt($amountWithoutTax * $item->getQuantity(), $currency)->getPriceInCurrencyUnits()
            )
            ->setAmountVat(
                Amount::fromInt($taxAmount, $currency)->getPriceInCurrencyUnits()
            )
            ->setTitle((string)$item->getProductName())
            ->setSubTitle($item->getProduct()?->getShortDescription())
            //   ->setImageUrl($this->getProductImage($item))
            ->setType(BasketItemTypes::GOODS);
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
            ->setAmountPerUnit(
                Amount::fromInt(abs($adjustment->getAmount()), $currency)->getPriceInCurrencyUnits()
            )
            ->setAmountGross(
                Amount::fromInt(abs($adjustment->getAmount()), $currency)->getPriceInCurrencyUnits()
            )
            ->setAmountNet(
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
