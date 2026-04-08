<?php

namespace Billink\Billink\Model\Billink\Request\Order;

class Item implements ItemInterface
{
    private string $code;

    private string $description;

    private int $quantity;

    private float $taxPercent;

    private string $priceType;

    private float $price;

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getTaxPercent(): float
    {
        return $this->taxPercent;
    }

    public function setTaxPercent(float $taxPercent): static
    {
        $this->taxPercent = $taxPercent;

        return $this;
    }

    public function getPriceType(): string
    {
        return $this->priceType;
    }

    public function setPriceType(string $priceType): static
    {
        $this->priceType = $priceType;

        return $this;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }
}
