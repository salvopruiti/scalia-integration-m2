<?php

namespace ScaliaGroup\Integration\Helper;

use Ess\M2ePro\Model\Amazon\Order\Item;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;

class OrderExporter
{
    public function export($id)
    {
        /** @var Order $order */
        $order = ObjectManager::getInstance()->create(\Magento\Sales\Model\OrderFactory::class)
            ->create()
            ->load($id);

        if (!$order->getEntityId()) {
            throw new NoSuchEntityException(
                __("The entity that was requested doesn't exist. Verify the entity and try again.")
            );
        }

        if (!$order->getStatus()) {
            throw new LocalizedException(
                __("Invalid Empty Order Status")
            );
        }

        $caratteri = ["'","‘",'"'];
        $this->m2eOrder = ObjectManager::getInstance()->get(\Ess\M2ePro\Model\OrderFactory::class);

        $id = $order->getId();
        $items = $order->getItemsCollection();
        $array_order_data = $order->getData();
        $array_address_shipping = $order->getShippingAddress();
        $array_address_billing = $order->getBillingAddress();
        $payment_method = $order->getPayment()->getData();
        $baseToOrderRate = $array_order_data['base_to_order_rate'];
        $currency = $array_order_data['order_currency_code'];
        $pickup_store = $array_order_data['pickup_store'] ?? null;
        $emailCliente = str_replace($caratteri," ", $array_order_data['customer_email']);
        $totale_pagato = $payment_method['base_amount_ordered'];
        $spese_spedizione = $array_order_data['shipping_invoiced'];
        $metodo_spedizione = $order->getShippingMethod();
        $dati_spedizione_indirizzo = implode("\n", array_unique($array_address_shipping->getStreet()));
        if(!empty($array_address_shipping['email'])){
            $emailCliente = str_replace($caratteri," ",$array_address_shipping['email']);
        }

        $order_currency_pagato = '';
        $order_currency_spedizione = '';
        $business = 0; // utente:  0 privato - 1 azienda
        $noteEbay = '';
        $order_items = [];
        $array_regalo = [];
        $m2eOrder = false;

        try {
            $m2eOrder = $this->m2eOrder->create()->load($id, 'magento_order_id');
        } catch (\Exception $e) {
            $m2eOrder = false;
        }

        if($m2eOrder) {

            $m2eOrderItem = ObjectManager::getInstance()->get(\Ess\M2ePro\Model\Order\Item::class)->load($m2eOrder->getId(), 'order_id');

            $amazonOrder = ObjectManager::getInstance()->get(\Ess\M2ePro\Model\Amazon\Order::class)->load($m2eOrder->getId(), 'order_id');
            $amazonOrderItem = ObjectManager::getInstance()->get(Item::class)->load($m2eOrderItem->getId(), 'order_item_id');


            $array_regalo = [[
                'gift_id' => 'gift_amazon-'.$order->getId(),
                'mittente' => ($amazonOrderItem['gift_message']!='') ?  'no-mittente' : 'none',
                'destinatario' => ($amazonOrderItem['gift_message']!='') ?  'no-destinatario' : 'none',
                'messaggio' => ($amazonOrderItem['gift_message']!='') ? $amazonOrderItem['gift_message'] : 'Confezione Regalo Non Attiva' //str_replace($caratteri, " ", $m2eproAmazonOrderItem['gift_message']),
            ]];

        } else {

            try {

                $giftMessage = ObjectManager::getInstance()->get(\Magento\GiftMessage\Api\OrderRepositoryInterface::class)->get($order->getId());


                $array_regalo = [
                    [
                        'gift_id' => $giftMessage->getGiftMessageId(),
                        'mittente' => str_replace($caratteri, " ", $giftMessage->getSender()) ?: "none",
                        'destinatario' => str_replace($caratteri, " ", $giftMessage->getRecipient() )?: "none",
                        'messaggio' => preg_replace("/\r|\n/", "", str_replace($caratteri, " ", $giftMessage->getMessage()))
                    ]];

            } catch (\Exception $e) {
                $array_gift_data = [];
                $array_regalo = [
                    [
                        'gift_id' => null,
                        'mittente' => 'none',
                        'destinatario' => 'none',
                        'messaggio' => 'Confezione Regalo Non Attiva'
                    ]
                ];

            }

        }

        /** @var Order\Item $item */
        foreach($items as $item) {
            if($item->getParentItem()) continue;

            $productOptions = $item->getData('product_options');
            $productId = $productOptions['info_buyRequest']['product'] ?? null;
            $productPrice = $item->getOriginalPrice();
            $originalPrice = $item->getOriginalPrice();
            $productP = ObjectManager::getInstance()->get(ProductRepository::class)->get($item->getSku());







            if($productP) {

                $productPId = $productP->getId();
                $productPrice = $productP->getPrice();
                $originalPrice = $productP->getPrice();
                $productPsku = $productP->getSku();
                $canale = $productP->hasData('supplier') ? $productP->getAttributeText('supplier') : null;
                $stagione = $productP->hasData('season') ? $productP->getAttributeText('season') : null;
                $codice_ean = $productP->getCodiceEan();
                $marchio = $productP->getAttributeText('manufacturer');
                $color = $productP->getAttributeText('color');
                $size = $productP->hasData('size') ? $productP->getAttributeText('size') : null;

                $parentIds = ObjectManager::getInstance()->create(\Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable::class)
                    ->getParentIdsByChild($productPId);

                $productConf = '';
                if (isset($parentIds[0])) {
                    $productConf = ObjectManager::getInstance()->create(ProductRepository::class)->getById($parentIds[0]);
                    //var_dump($productConf->getSku());
                    $productConf = $productConf->getSku();
                }

            } else {
                $productPId = '';
                $productPrice = '';
                $originalPrice = '';
                $productPsku = '';
                $canale = '';
                $barcode = '';
                $stagione = '';
                $codice_ean = '';
                $marchio = '';
                $gruppo = '';
                $color = '';
                $size = '';
            }

            for($i = 1; $i<= $item->getQtyOrdered(); $i++) {
                $order_items[] = array(
                    'idOrder' => $id,
                    //'idt'   => $order->getId(),
                    'idIncrement' => $order->getIncrementId(),
                    //'idProduct'     => $item->getId(),
                    'idProduct' => $productPId,
                    'name' => str_replace($caratteri, " ", $item->getName()),
                    'sku' => $productPsku,
                    'price' => $productPrice,
                    'canale' => $canale,
                    'stagione' => $stagione,
                    'codice_ean' => $codice_ean,
                    'marchio' => $marchio,
                    'color' => $color,
                    'size' => $size,
                    'finalPriceInclTax' => $item->getData('base_price_incl_tax'),
                    'originalPrice' => $originalPrice,
                    'taxAmount' => $item->getData('tax_amount'),
                    'orderedQty' => 1
                );
            }
        }

        return [
                'products' =>  $order_items,
                'entity_id' => $array_order_data['entity_id'],
                'order_id' => $array_order_data['increment_id'],
                'cliente' => str_replace($caratteri," ",$array_order_data['customer_firstname'] ?: $array_address_billing['firstname']).' '.str_replace($caratteri," ",$array_order_data['customer_lastname'] ?: $array_address_billing['lastname']),
                'cliente_nome' => str_replace($caratteri," ",$array_order_data['customer_firstname'] ?: $array_address_billing['firstname']),
                'cliente_cognome' => str_replace( $caratteri," ",$array_order_data['customer_lastname'] ?: $array_address_billing['lastname']),
                'data_ordine' => $array_order_data['created_at'],
                'dati_spedizione_cliente' => str_replace($caratteri," ",$array_address_shipping['firstname'])." ".str_replace($caratteri," ",$array_address_shipping['lastname']),
                'dati_spedizione_indirizzo' => $dati_spedizione_indirizzo,
                'dati_spedizione_email' => $emailCliente,
                'dati_spedizione_telefono' => str_replace($caratteri," ",$array_address_shipping['telephone']),

                //,   'dati_fatturazione' => $array_dati_fatturazione = json_encode(array(
                'dati_fatturazione_cliente' => str_replace($caratteri," ",$array_address_billing['firstname'])." ".str_replace($caratteri," ",$array_address_billing['lastname']),
                'dati_fatturazione_indirizzo' => str_replace($caratteri," ",$array_address_billing['street'])." ".str_replace($caratteri," ",$array_address_billing['city'])." ".str_replace($caratteri," ",$array_address_billing['postcode'])." ".str_replace($caratteri," ",$array_address_billing['region'])." ".str_replace($caratteri," ",$array_address_billing['country_id']),
                'dati_fatturazione_email' => str_replace($caratteri," ",$array_address_billing['email']),
                'dati_fatturazione_telefono' => str_replace($caratteri," ",$array_address_billing['telephone']),
                //    ))
                'spese_spedizione' => $spese_spedizione,
                'regalo' => $array_regalo,

                'status' => $array_order_data['status'],
                'metodo_pagamento' => $payment_method['method'],
                'totale_pagato' => number_format($totale_pagato, 2 , '.', ''),
                'coupon' => $array_order_data['coupon_code'],

                'dati_spedizione_city' => str_replace($caratteri," ",$array_address_shipping['city']),
                'dati_spedizione_postcode' => str_replace($caratteri," ",$array_address_shipping['postcode']),
                'dati_spedizione_region' => str_replace($caratteri," ",$array_address_shipping['region']),
                'dati_spedizione_country_id' => str_replace($caratteri," ",$array_address_shipping['country_id']),
                'business' => $business,
                'note_ebay' => $noteEbay,
                'currency' => $currency,
                'order_currency_pagato' => $order_currency_pagato,
                'order_currency_spedizione' => $order_currency_spedizione,
                'base_to_order_rate' => $baseToOrderRate,
                'coupon_name' => $array_order_data['coupon_rule_name'],
                'coupon_descr' => $array_order_data['discount_description'],
                'coupon_value' => $array_order_data['discount_amount'],

                'pickup_store' => $pickup_store,
                'metodo_spedizione' => $metodo_spedizione
        ];

    }
}
