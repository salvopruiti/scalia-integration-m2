<?php

namespace ScaliaGroup\Integration\Api\Data;

interface OrderInterface
{
    /**
     * @return \ScaliaGroup\Integration\Api\Data\OrderProductInterface[]
     */
    public function getProducts();

    /**
     * @param object $products
     * @return $this
     */
    public function setProducts($products);

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @return mixed
     */
    public function getOrderId();

    /**
     * @return string
     */
    public function getCliente();

    /**
     * @return mixed
     */
    public function getClienteNome();

    /**
     * @return mixed
     */
    public function getClienteCognome();

    /**
     * @return \DateTime
     */
    public function getDataOrdine();

    /**
     * @return mixed
     */
    public function getDatiSpedizioneCliente();

    /**
     * @return mixed
     */
    public function getDatiSpedizioneIndirizzo();

    /**
     * @return mixed
     */
    public function getDatiSpedizioneEmail();

    /**
     * @return mixed
     */
    public function getDatiSpedizioneTelefono();

    /**
     * @return mixed
     */
    public function getDatiFatturazioneCliente();

    /**
     * @return mixed
     */
    public function getDatiFatturazioneIndirizzo();

    /**
     * @return mixed
     */
    public function getDatiFatturazioneEmail();

    /**
     * @return mixed
     */
    public function getDatiFatturazioneTelefono();

    /**
     * @return float
     */
    public function getSpeseSpedizione();

    /**
     * @return \ScaliaGroup\Integration\Api\Data\OrderGiftMessageInterface[]
     */
    public function getRegalo();

    /**
     * @return mixed
     */
    public function getStatus();

    /**
     * @return mixed
     */
    public function getMetodoPagamento();

    /**
     * @return float
     */
    public function getTotalePagato();

    /**
     * @return mixed
     */
    public function getCoupon();

    /**
     * @return mixed
     */
    public function getDatiSpedizioneCity();

    /**
     * @return mixed
     */
    public function getDatiSpedizionePostcode();

    /**
     * @return mixed
     */
    public function getDatiSpedizioneRegion();

    /**
     * @return mixed
     */
    public function getDatiSpedizioneCountryId();

    /**
     * @return mixed
     */
    public function getBusiness();

    /**
     * @return mixed
     */
    public function getNoteEbay();

    /**
     * @return mixed
     */
    public function getCurrency();

    /**
     * @return mixed
     */
    public function getOrderCurrencyPagato();

    /**
     * @return mixed
     */
    public function getOrderCurrencySpedizione();

    /**
     * @return float
     */
    public function getBaseToOrderRate();

    /**
     * @return mixed
     */
    public function getCouponName();

    /**
     * @return mixed
     */
    public function getCouponDescr();

    /**
     * @return float
     */
    public function getCouponValue();

    /**
     * @return mixed
     */
    public function getPickupStore();

    /**
     * @return mixed
     */
    public function getMetodoSpedizione();

    /**
     * @return string|null
     */
    public function getDatiSpedizioneAzienda();

    /**
     * @return string|null
     */
    public function getDatiFatturazioneAzienda();

    /**
     * @return mixed
     */
    public function getDatiFatturazioneCity();

    /**
     * @return mixed
     */
    public function getDatiFatturazionePostcode();

    /**
     * @return mixed
     */
    public function getDatiFatturazioneRegion();

    /**
     * @return mixed
     */
    public function getDatiFatturazioneCountryId();

}
