<?php

namespace Billink\Billink\Model\Billink\Request\Order;


/**
 * Class Item
 * @package Billink\Billink\Model\Billink\Request\Order
 */
interface ItemInterface
{
    /**
     * @return mixed
     */
    public function getCode();

    /**
     * @param mixed $code
     * @return $this
     */
    public function setCode($code);

    /**
     * @return mixed
     */
    public function getDescription();

    /**
     * @param mixed $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * @return mixed
     */
    public function getQuantity();

    /**
     * @param mixed $quantity
     * @return $this
     */
    public function setQuantity($quantity);

    /**
     * @return mixed
     */
    public function getTaxPercent();

    /**
     * @param mixed $taxPercent
     * @return $this
     */
    public function setTaxPercent($taxPercent);

    /**
     * @return mixed
     */
    public function getPriceType();

    /**
     * @param mixed $priceType
     * @return $this
     */
    public function setPriceType($priceType);

    /**
     * @return mixed
     */
    public function getPrice();

    /**
     * @param mixed $price
     * @return $this
     */
    public function setPrice($price);
}