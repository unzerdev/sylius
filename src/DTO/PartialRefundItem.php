<?php


declare(strict_types=1);

namespace SyliusUnzerPlugin\DTO;

final class PartialRefundItem
{
    /** @var int */
    private int $id;

    /** @var string */
    private string $type;

    /** @var int */
    private int $amountTotal = 0;

    /** @var int */
    private int $amountRefunded = 0;

    /** @var int */
    private int $quantity = 1;

    /** @var int */
    private int $amountToRefund = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getAmountTotal(): int
    {
        return $this->amountTotal;
    }

    public function setAmountTotal(int $amountTotal): void
    {
        $this->amountTotal = $amountTotal;
    }

    public function getAmountRefunded(): int
    {
        return $this->amountRefunded;
    }

    public function setAmountRefunded(int $amountRefunded): void
    {
        $this->amountRefunded += $amountRefunded;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getAvailableAmountToRefund(): int
    {
        return $this->getAmountTotal() - $this->getAmountRefunded() - $this->getAmountToRefund();
    }

    public function setAmountToRefund(int $amount): int
    {
        $value = $this->getAvailableAmountToRefund() - $amount;

        if (0 > $value) {
            $this->amountToRefund = $this->getAvailableAmountToRefund();

            return abs($value);
        }

        $this->amountToRefund = $amount;

        return 0;
    }

    public function getAmountToRefund(): int
    {
        return $this->amountToRefund;
    }
}
