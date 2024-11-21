<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Services\Integration\PaymentPage\Processors;

use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Unzer\Core\BusinessLogic\Domain\Integration\PaymentPage\Processors\CustomerProcessor as CustomerProcessorInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentPage\Models\PaymentPageCreateContext;
use UnzerSDK\Constants\Salutations;
use UnzerSDK\Resources\Customer;
use UnzerSDK\Resources\EmbeddedResources\Address;

/**
 * Class CustomerProcessor
 *
 * @package SyliusUnzerPlugin\Services\Integration\PaymentPage\Processors
 */
class CustomerProcessor implements CustomerProcessorInterface
{
    /** @var ChannelContextInterface $channelContext */
    private ChannelContextInterface $channelContext;

    /**
     * @param ChannelContextInterface $channelContext
     */
    public function __construct(ChannelContextInterface $channelContext)
    {
        $this->channelContext = $channelContext;
    }

    public function process(Customer $customer, PaymentPageCreateContext $context): void
    {
        if (!$this->shouldProcess($context)) {
            return;
        }

        /** @var OrderInterface $order */
        $order = $context->getCheckoutSession()->get('order');

        $hostname = $this->channelContext->getChannel()->getHostname() ?? '';
        $domain = str_replace(['http://', 'https://'], '', $hostname);
        $email = $order->getCustomer() !== null && $order->getCustomer()->getEmail() !== null ?
            $order->getCustomer()->getEmail() : '';
        /** @var string $customerId */
        $customerId = $order->getCustomer() !== null ? (string)$order->getCustomer()->getId() . '_' . $email . '_' . $domain : null;

        $customer
            ->setCustomerId(sha1($customerId))
            ->setFirstname($order->getCustomer()?->getFirstName())
            ->setLastname($order->getCustomer()?->getLastName())
            ->setSalutation($this->getSalutation($order->getCustomer()))
            ->setCompany($order->getBillingAddress()?->getCompany())
            ->setBirthDate($order->getCustomer()?->getBirthday()?->format('Y-m-d'))
            ->setEmail($order->getCustomer()?->getEmail())
            ->setPhone($order->getCustomer()?->getPhoneNumber())
            ->setBillingAddress($this->mapAddress($order->getBillingAddress()))
            ->setShippingAddress($this->mapAddress($order->getShippingAddress()));
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

        /** @var OrderInterface $order */
        if ($order->isCreatedByGuest() || null === $order->getCustomer()) {
            return false;
        }

        return true;
    }

    private function getSalutation(?CustomerInterface $customer): ?string
    {
        if (null === $customer) {
            return null;
        }

        if ($customer->isMale()) {
            return Salutations::MR;
        }

        if ($customer->isFemale()) {
            return Salutations::MRS;
        }

        return null;
    }

    private function mapAddress(?AddressInterface $address): Address
    {
        $result = new Address();

        if (null === $address) {
            return $result;
        }

        return $result
            ->setName($address->getFirstName() . ' ' . $address->getLastName())
            ->setStreet($address->getStreet())
            ->setZip($address->getPostcode())
            ->setCity($address->getCity())
            ->setCountry($address->getCountryCode());
    }
}
