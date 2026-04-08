<?php

namespace Billink\Billink\Model\Billink\Request\Order;

interface ItemInterface
{
    public function getCode(): string;

    public function setCode(string $code): static;

    public function getDescription(): string;

    public function setDescription(string $description): static;

    public function getQuantity(): int;

    public function setQuantity(int $quantity): static;

    public function getTaxPercent(): float;

    public function setTaxPercent(float $taxPercent): static;

    public function getPriceType(): string;

    public function setPriceType(string $priceType): static;

    public function getPrice(): float;

    public function setPrice(float $price): static;
}
