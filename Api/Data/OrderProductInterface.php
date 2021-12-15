<?php

namespace ScaliaGroup\Integration\Api\Data;

interface OrderProductInterface
{
    /**
     * @return int
     */
    public function getIdOrder();

    /**
     * @param int $idOrder
     * @return $this
     */
    public function setIdOrder($idOrder);

    /**
     * @return string
     */
    public function getIdIncrement();

    /**
     * @param string $idIncrement
     * @return $this
     */
    public function setIdIncrement($idIncrement);

    /**
     * @return int
     */
    public function getIdProduct();

    /**
     * @param $idProduct
     * @return $this
     */
    public function setIdProduct($idProduct);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getSku();

    /**
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);

    /**
     * @return string
     */
    public function getCleanSku();

    /**
     * @param string $clean_sku
     * @return $this
     */
    public function setCleanSku($clean_sku);

    /**
     * @return float
     */
    public function getPrice();

    /**
     * @param float $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * @return string|null
     */
    public function getCanale();

    /**
     * @param string $canale
     * @return $this
     */
    public function setCanale($canale);

    /**
     * @return string|null
     */
    public function getStagione();

    /**
     * @param $stagione
     * @return $this
     */
    public function setStagione($stagione);

    /**
     * @return string
     */
    public function getCodiceEan();

    /**
     * @param string $codice_ean
     * @return $this
     */
    public function setCodiceEan($codice_ean);

    /**
     * @return string
     */
    public function getMarchio();

    /**
     * @param $marchio
     * @return $this
     */
    public function setMarchio($marchio);

    /**
     * @return mixed
     */
    public function getColor();

    /**
     * @param $color
     * @return $this
     */
    public function setColor($color);

    /**
     * @return mixed
     */
    public function getSize();

    /**
     * @param $size
     * @return $this
     */
    public function setSize($size);

    /**
     * @return float
     */
    public function getFinalPriceInclTax();

    /**
     * @param $finalPriceInclTax
     * @return $this
     */
    public function setFinalPriceInclTax($finalPriceInclTax);

    /**
     * @return float
     */
    public function getOriginalPrice();

    /**
     * @param $originalPrice
     * @return $this
     */
    public function setOriginalPrice($originalPrice);

    /**
     * @return float
     */
    public function getTaxAmount();

    /**
     * @param $taxAmount
     * @return $this
     */
    public function setTaxAmount($taxAmount);

    /**
     * @return mixed
     */
    public function getOrderedQty();

    /**
     * @param $orderedQty
     * @return $this
     */
    public function setOrderedQty($orderedQty);
}
