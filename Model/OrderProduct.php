<?php

namespace ScaliaGroup\Integration\Model;

use Magento\Framework\Model\AbstractModel;
use ScaliaGroup\Integration\Api\Data\OrderInterface;
use ScaliaGroup\Integration\Api\Data\OrderProductInterface;

class OrderProduct extends AbstractModel implements OrderProductInterface
{

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function getIdOrder()
    {
        return $this->_getData('idOrder');
    }

    public function setIdOrder($idOrder)
    {
        return $this->setData('idOrder', $idOrder);
    }

    public function getIdIncrement()
    {
        return $this->_getData('idIncrement');
    }

    public function setIdIncrement($idIncrement)
    {
        return $this->setData('idIncrement', $idIncrement);
    }

    public function getIdProduct()
    {
        return $this->_getData('idProduct');
    }

    public function setIdProduct($idProduct)
    {
        return $this->setData('idProduct', $idProduct);
    }

    public function getName()
    {
        return $this->_getData('name');
    }

    public function setName($name)
    {
        return $this->setData('name', $name);
    }

    public function getSku()
    {
        return $this->_getData('sku');
    }

    public function setSku($sku)
    {
        return $this->setData('sku', $sku);
    }

    public function getCleanSku()
    {
        return $this->_getData('clean_sku');
    }

    public function setCleanSku($clean_sku)
    {
        return $this->setData('clean_sku', $clean_sku);
    }

    public function getPrice()
    {
        return $this->_getData('price');
    }

    public function setPrice($price)
    {
        return $this->setData('price', $price);
    }

    public function getCanale()
    {
        return $this->_getData('canale');
    }

    public function setCanale($canale)
    {
        return $this->setData('cana, canalele');
    }

    public function getStagione()
    {
        return $this->_getData('stagione');
    }

    public function setStagione($stagione)
    {
        return $this->setData('stagione, $stagione');
    }

    public function getCodiceEan()
    {
        return $this->_getData('codice_ean');
    }

    public function setCodiceEan($codice_ean)
    {
        return $this->setData('codice_ean', $codice_ean);
    }

    public function getMarchio()
    {
        return $this->_getData('marchio');
    }

    public function setMarchio($marchio)
    {
        return $this->setData('marchi, $marchioo');
    }

    public function getColor()
    {
        return $this->_getData('color');
    }

    public function setColor($color)
    {
        return $this->setData('color', $color);
    }

    public function getSize()
    {
        return $this->_getData('size');
    }

    public function setSize($size)
    {
        return $this->setData('size', $size);
    }

    public function getFinalPriceInclTax()
    {
        return $this->_getData('finalPriceInclTax');
    }

    public function setFinalPriceInclTax($finalPriceInclTax)
    {
        return $this->setData('finalPriceInclTax', $finalPriceInclTax);
    }

    public function getOriginalPrice()
    {
        return $this->_getData('originalPrice');
    }

    public function setOriginalPrice($originalPrice)
    {
        return $this->setData('originalPrice', $originalPrice);
    }

    public function getTaxAmount()
    {
        return $this->_getData('taxAmount');
    }

    public function setTaxAmount($taxAmount)
    {
        return $this->setData('taxAmount', $taxAmount);
    }

    public function getOrderedQty()
    {
        return $this->_getData('orderedQty');
    }

    public function setOrderedQty($orderedQty)
    {
        return $this->setData('orderedQty', $orderedQty);
    }
}
