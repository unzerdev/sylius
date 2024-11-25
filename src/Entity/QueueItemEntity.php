<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Entity;

use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * Class QueueItemEntity.
 *
 * @package SyliusUnzerPlugin\Entity
 */
class QueueItemEntity implements ResourceInterface
{
    /**
     * @var int
     */
    protected int $id;

    /**
     * @var string
     */
    protected string $type;

    /**
     * @var ?string
     */
    protected ?string $index1;

    /**
     * @var ?string
     */
    protected ?string $index2;

    /**
     * @var ?string
     */
    protected ?string $index3;

    /**
     * @var ?string
     */
    protected ?string $index4;

    /**
     * @var ?string
     */
    protected ?string $index5;

    /**
     * @var ?string
     */
    protected ?string $index6;

    /**
     * @var ?string
     */
    protected ?string $index7;

    /**
     * @var ?string
     */
    protected ?string $index8;

    /**
     * @var ?string
     */
    protected ?string $index9;

    /**
     * @var string
     */
    protected string $data;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return void
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return ?string
     */
    public function getIndex1(): ?string
    {
        return $this->index1;
    }

    /**
     * @param ?string $index1
     */
    public function setIndex1(?string $index1): void
    {
        $this->index1 = $index1;
    }

    /**
     * @return ?string
     */
    public function getIndex2(): ?string
    {
        return $this->index2;
    }

    /**
     * @param ?string $index2
     */
    public function setIndex2(?string $index2): void
    {
        $this->index2 = $index2;
    }

    /**
     * @return ?string
     */
    public function getIndex3(): ?string
    {
        return $this->index3;
    }

    /**
     * @param ?string $index3
     */
    public function setIndex3(?string $index3): void
    {
        $this->index3 = $index3;
    }

    /**
     * @return ?string
     */
    public function getIndex4(): ?string
    {
        return $this->index4;
    }

    /**
     * @param ?string $index4
     */
    public function setIndex4(?string $index4): void
    {
        $this->index4 = $index4;
    }

    /**
     * @return ?string
     */
    public function getIndex5(): ?string
    {
        return $this->index5;
    }

    /**
     * @param ?string $index5
     */
    public function setIndex5(?string $index5): void
    {
        $this->index5 = $index5;
    }

    /**
     * @return ?string
     */
    public function getIndex6(): ?string
    {
        return $this->index6;
    }

    /**
     * @param ?string $index6
     */
    public function setIndex6(?string $index6): void
    {
        $this->index6 = $index6;
    }

    /**
     * @return ?string
     */
    public function getIndex7(): ?string
    {
        return $this->index7;
    }

    /**
     * @param ?string $index7
     */
    public function setIndex7(?string $index7): void
    {
        $this->index7 = $index7;
    }

    /**
     * @return ?string
     */
    public function getIndex8(): ?string
    {
        return $this->index8;
    }

    /**
     * @param ?string $index8
     */
    public function setIndex8(?string $index8): void
    {
        $this->index8 = $index8;
    }

    /**
     * @return ?string
     */
    public function getIndex9(): ?string
    {
        return $this->index9;
    }

    /**
     * @param ?string $index9
     *
     * @return void
     */
    public function setIndex9(?string $index9): void
    {
        $this->index9 = $index9;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @param string $data
     */
    public function setData(string $data): void
    {
        $this->data = $data;
    }
}
