<?php

namespace ScaliaGroup\Integration\Model;

use Magento\Framework\Model\AbstractModel;
use ScaliaGroup\Integration\Api\Data\OrderInterface;

class Order extends AbstractModel implements OrderInterface
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

    public function getProducts()
    {
        return $this->getData('products');
    }

    public function setProducts($products)
    {
        return $this->setData('products', $products);
    }

    public function getOrderId()
    {
        return $this->_getData('order_id');
    }

    public function getCliente()
    {
        return $this->_getData('cliente');
    }

    public function getClienteNome()
    {
        return $this->_getData('cliente_nome');
    }

    public function getClienteCognome()
    {
        return $this->_getData('cliente_cognome');

    }

    public function getDataOrdine()
    {
        return $this->_getData('data_ordine');
    }

    public function getDatiSpedizioneCliente()
    {
        return $this->_getData('dati_spedizione_cliente');
    }

    public function getDatiSpedizioneIndirizzo()
    {
        return $this->_getData('dati_spedizione_indirizzo');
    }

    public function getDatiSpedizioneEmail()
    {
        return $this->_getData('dati_spedizione_email');
    }

    public function getDatiSpedizioneTelefono()
    {
        return $this->_getData('dati_spedizione_telefono');
    }

    public function getDatiFatturazioneCliente()
    {
        return $this->_getData('dati_fatturazione_cliente');
    }

    public function getDatiFatturazioneIndirizzo()
    {
        return $this->_getData('dati_fatturazione_indirizzo');
    }

    public function getDatiFatturazioneEmail()
    {
        return $this->_getData('dati_fatturazione_email');
    }

    public function getDatiFatturazioneTelefono()
    {
        return $this->_getData('dati_fatturazione_telefono');
    }

    public function getSpeseSpedizione()
    {
        return $this->_getData('spese_spedizione');
    }

    public function getRegalo()
    {
        return $this->_getData('regalo');
    }

    public function getStatus()
    {
        return $this->_getData('status');
    }

    public function getMetodoPagamento()
    {
        return $this->_getData('metodo_pagamento');
    }

    public function getTotalePagato()
    {
        return $this->_getData('totale_pagato');
    }

    public function getCoupon()
    {
        return $this->_getData('coupon');
    }

    public function getDatiSpedizioneCity()
    {
        return $this->_getData('dati_spedizione_city');
    }

    public function getDatiSpedizionePostcode()
    {
        return $this->_getData('dati_spedizione_postcode');
    }

    public function getDatiSpedizioneRegion()
    {
        return $this->_getData('dati_spedizione_region');
    }

    public function getDatiSpedizioneCountryId()
    {
        return $this->_getData('dati_spedizione_country_id');
    }

    public function getBusiness()
    {
        return $this->_getData('business');
    }

    public function getNoteEbay()
    {
        return $this->_getData('note_ebay');
    }

    public function getCurrency()
    {
        return $this->_getData('currency');
    }

    public function getOrderCurrencyPagato()
    {
        return $this->_getData('order_currency_pagato');
    }

    public function getOrderCurrencySpedizione()
    {
        return $this->_getData('order_currency_spedizione');
    }

    public function getBaseToOrderRate()
    {
        return $this->_getData('base_to_order_rate');
    }

    public function getCouponName()
    {
        return $this->_getData('coupon_name');
    }

    public function getCouponDescr()
    {
        return $this->_getData('coupon_descr');
    }

    public function getCouponValue()
    {
        return $this->_getData('coupon_value');
    }

    public function getPickupStore()
    {
        return $this->_getData('pickup_store');
    }

    public function getMetodoSpedizione()
    {
        return $this->_getData('metodo_spedizione');
    }

    public function getDatiSpedizioneAzienda()
    {
        return $this->_getData('dati_spedizione_azienda');
    }

    public function getDatiFatturazioneAzienda()
    {
        return $this->_getData('dati_fatturazione_azienda');
    }
}
