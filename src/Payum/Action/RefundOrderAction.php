<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Sylius\RefundPlugin\Model\OrderItemUnitRefund;
use Sylius\RefundPlugin\Model\ShipmentRefund;
use Payum\Core\Bridge\Spl\ArrayObject;
use SyliusUnzerPlugin\Handler\Request\RefundOrder;
use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request\RefundRequest;
use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\CurrencyMismatchException;
use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\InvalidCurrencyCode;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Exceptions\RefundNotPossibleException;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidTransactionHistory;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\TransactionHistoryNotFoundException;
use UnzerSDK\Exceptions\UnzerApiException;

final class RefundOrderAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;


    /** @inheritdoc  */
    public function execute($request): void
    {
        if (! $request instanceof RefundOrder) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }
        RequestNotSupportedException::assertSupports($this, $request);

        $details = ArrayObject::ensureArrayObject($request->getModel());
        /** @var array $metaData */
        $metaData =  $details['metadata'];
        if (!array_key_exists('refund',$metaData)) {
            return;
        }

        $refundData = $metaData['refund'];

        try {
            AdminAPI::get()->order($refundData['channelId'])->refund(
                new RefundRequest(
                    $refundData['orderId'],
                    Amount::fromInt($refundData['refundedTotal'],
                    Currency::fromIsoCode($refundData['currencyCode']))
                )
            );
        } catch (UnzerApiException $e) {
        } catch (CurrencyMismatchException $e) {
        } catch (ConnectionSettingsNotFoundException $e) {
        } catch (RefundNotPossibleException $e) {
        } catch (InvalidTransactionHistory $e) {
        } catch (TransactionHistoryNotFoundException $e) {
        } catch (InvalidCurrencyCode $e) {
        }
    }

    public function convert(array $data): int
    {
        $value = 0;

        foreach ($data as $items) {
            foreach ($this->getTotal($items) as $total) {
                $value += $total;
            }
        }

        return $value;
    }

    private function getTotal(array $refundsData): iterable
    {
        /** @var OrderItemUnitRefund|ShipmentRefund $refundData */
        foreach ($refundsData as $refundData) {
            yield $refundData->total();
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof RefundOrder &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
