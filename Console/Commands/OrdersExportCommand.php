<?php

namespace ScaliaGroup\Integration\Console\Commands;

use Laminas\Http\Client;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem;
use Magento\GiftMessage\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Swatches\Helper\Media;
use Magento\Swatches\Model\ResourceModel\Swatch\Collection;
use ScaliaGroup\Integration\Helper\Config;
use ScaliaGroup\Integration\Logger\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OrdersExportCommand extends Command
{

    protected $connection;
    private $logger;
    private $config;
    private $eavConfig;
    private $csv;
    private $directoryList;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param Config $config
     * @param Logger $logger
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param Csv $csv
     * @param string|null $name
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        Config                                    $config,
        Logger                                    $logger,
        \Magento\Eav\Model\Config                 $eavConfig,
        Csv                                       $csv,
        DirectoryList                             $directoryList,
        string                                    $name = null
    )
    {
        parent::__construct($name);

        $this->connection = $resource->getConnection();
        $this->logger = $logger;
        $this->config = $config;
        $this->eavConfig = $eavConfig;
        $this->state = ObjectManager::getInstance()->get(State::class);
        $this->csv = $csv;
        $this->directoryList = $directoryList;
    }


    protected function configure()
    {
        $this->setName('sg:orders:export');
        $this->setDescription("Export Orders To Middleware");
        $this->addOption("date", "d", InputOption::VALUE_OPTIONAL);
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $host = $this->config->getMiddlewareHost();
        $access_token = $this->config->getMiddlewareAccessToken();


        $salesOrderCollection = ObjectManager::getInstance()->create(\Magento\Sales\Model\ResourceModel\Order\Collection::class);

        if($date = $input->getOption('date')) {
            $date = new \DateTime($date);
        } else {
            $date = (new \DateTime())->modify('- 1 day');
        }

        $salesOrderCollection->addFieldToFilter('created_at', ['gteq' => $date]);

        $this->exportForSap($salesOrderCollection, $output);

        return;

        /** @var Order $order */
        foreach ($salesOrderCollection as $order) {

            try {

                if($this->config->getIsDebugMode())
                    $this->logger->debug("Invio Ordine", ['order' => $order->toArray()]);


                $output->write('Invio Ordine '. $order->getIncrementId() .'... ');

                $client = new Client();
                $client->setUri("$host/api/v1/orders")
                    ->setHeaders([
                        'Accept' => 'application/json',
                        'Authorization' => "Bearer $access_token"
                    ]);

                $client->setMethod('post');
                $client->setParameterGet($parameters = [
                    'type' => 'order',
                    'entity_id' => $order->getId()
                ]);

                $response = $client->send();

                if ($response->getStatusCode() != 200) {
                    $this->logger->error('ColorsSyncCommand', [
                        'status' => $response->getStatusCode(),
                        'phrase' => $response->getReasonPhrase(),
                        'body' => $response->getBody()
                    ]);

                    if($this->config->getIsDebugMode())
                        dd($response->getBody());


                    die("Invalid Response from Middleware! See sg_integration log\n");
                }

                $output->writeln('<info>OK</info>');

            } catch (\Exception $e) {

                $this->logger->error('ColorsSyncCommand', [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]);
                die("Invalid Response from Middleware! See sg_integration log");

            }
        }





    }

    protected function getColorAttribute()
    {
        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, 'color');
        return $attribute;
    }

    protected function getAvailableColors($attribute_id)
    {
        $query = $this->connection->select()
            ->from(['eav_attribute_option'], 'option_id as id')
            ->joinLeft('eav_attribute_option_value', 'eav_attribute_option.option_id = eav_attribute_option_value.option_id',
                'value as name'
            )
//            ->reset(\Zend_Db_Select::COLUMNS)
//            ->columns('eav_attribute_option_value.option_id, eav_attribute_option.value as color_name')
            ->where('eav_attribute_option.attribute_id = ?', [$attribute_id]);

        $colors = [];

        foreach ($this->connection->fetchAll($query) as $color) {
            $colors[mb_strtoupper($color['name'])] = $color['id'];
        }

        return $colors;
    }


    public function generateOptions($values, $swatchType = 'visual')
    {
        $i = 0;
        foreach($values as $key => $value) {
            $order["option_{$i}"] = $i;
            $optionsStore["option_{$i}"] = array(
                0 => $key, // admin
                1 => $value['label'], // default store view
            );
            $textSwatch["option_{$i}"] = array(
                1 => $value['label'],
            );
            $visualSwatch["option_{$i}"] = $value['url'];
            $delete["option_{$i}"] = '';
            $i++;
        }

        switch($swatchType)
        {
            case 'text':
                return [
                    'optiontext' => [
                        'order'     => $order,
                        'value'     => $optionsStore,
                        'delete'    => $delete,
                    ],
                    'swatchtext' => [
                        'value'     => $textSwatch,
                    ],
                ];
                break;
            case 'visual':
                return [
                    'optionvisual' => [
                        'order'     => $order,
                        'value'     => $optionsStore,
                        'delete'    => $delete,
                    ],
                    'swatchvisual' => [
                        'value'     => $visualSwatch,
                    ],
                ];
                break;
            default:
                return [
                    'option' => [
                        'order'     => $order,
                        'value'     => $optionsStore,
                        'delete'    => $delete,
                    ],
                ];
        }
    }

    protected function exportForSap($salesOrderCollection, $output)
    {
        $caratteri = ["'","‘",'"'];

        // SAP
        $array_items_order = [];

        // MIDDLEWARE OLD SCRIPT
        $array_dati_ordine = [];

        /** @var Order $order */
        foreach($salesOrderCollection as $order) {

            $output->write('Invio Ordine '. $order->getIncrementId() ."...\n");

            $id = $order->getId();
            $items = $order->getItemsCollection();
            $array_order_data = $order->getData();
            $array_address_shipping = $order->getShippingAddress();
            $array_address_billing = $order->getBillingAddress();
            $payment_method = $order->getPayment()->getData();

            $baseToOrderRate = $array_order_data['base_to_order_rate'];
            $currency = $array_order_data['order_currency_code'];

            $emailCliente = str_replace($caratteri," ", $array_order_data['customer_email']);

            $totale_pagato = $payment_method['base_amount_ordered'];
            $spese_spedizione = $array_order_data['shipping_invoiced'];

            if(!empty($array_address_shipping['email'])){
                $emailCliente = str_replace($caratteri," ",$array_address_shipping['email']);
            }

            $order_currency_pagato = '';
            $order_currency_spedizione = '';
            $business = 0; // utente:  0 privato - 1 azienda
            $noteEbay = '';

            $line = 1;

            $order_items = [];

            /** @var Order\Item $item */
            foreach($items as $item) {
                if($item->getParentItem()) continue;

                $productOptions = $item->getData('product_options');
                $productId = $productOptions['info_buyRequest']['product'];
                $productPrice = $item->getOriginalPrice();
                $originalPrice = $item->getOriginalPrice();

                $productP = ObjectManager::getInstance()->get(ProductRepository::class)->get($item->getSku());

                try {
                    $array_gift_data = ObjectManager::getInstance()->get(OrderRepositoryInterface::class)->get($order->getId());
                } catch (\Exception $e) {
                    $array_gift_data = [];
                }

                $array_regalo = array(
                    'gift_id' => (!empty($array_gift_data)) ? $array_order_data['gift_message_id'] : 'gift_store-' . $id,
                    'mittente' => (!empty($array_gift_data)) ? str_replace($caratteri, " ", $array_gift_data['sender']) : 'none',
                    'destinatario' => (!empty($array_gift_data)) ? str_replace($caratteri, " ", $array_gift_data['recipient']) : 'none',
                    'messaggio' => (!empty($array_gift_data)) ? preg_replace("/\r|\n/", "", str_replace($caratteri, " ", $array_gift_data['message'])) : 'Confezione Regalo',
                );


                if($productP) {

                    $productPId = $productP->getId();
                    $productPrice = $productP->getPrice();
                    $originalPrice = $productP->getPrice();
                    $productPsku = $productP->getSku();
                    $canale = $productP->getAttributeText('supplier');
                    $stagione = $productP->getAttributeText('season');
                    $codice_ean = $productP->getCodiceEan();
                    $marchio = $productP->getAttributeText('manufacturer');
                    $color = $productP->getAttributeText('color');
                    $size = $productP->getAttributeText('size');

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

                $array_items_order[] = [
                    'order_id'      => $id,
                    'idIncrement'   => $order->getIncrementId(),
                    'line_id'   => $line,
                    'idProduct'     => $productPId,
                    'name'          => $item->getName(),
                    'sku'           => $productPsku,

                    'codebars'       => null,
                    'codice_ean'       => $codice_ean,
                    'supplier'       => $canale,

                    'Price'         => $productPrice,
                    'FinalPriceInclTax'    => $item->getData('base_price_incl_tax'),
                    'OriginalPrice' => $originalPrice,
                    'TaxAmount' => $item->getData('tax_amount'),
                    'Ordered Qty'   => $item->getQtyOrdered(),
                    'cliente' => str_replace($caratteri," ",$array_order_data['customer_firstname']).' '.str_replace($caratteri," ",$array_order_data['customer_lastname']),
                    'cliente_nome' => str_replace($caratteri," ",$array_order_data['customer_firstname']),
                    'cliente_cognome' => str_replace( $caratteri," ",$array_order_data['customer_lastname']),

                    'data_ordine' => $array_order_data['created_at'],
                    'dati_spedizione_cliente' => preg_replace( "/\r|\n/", "", str_replace($caratteri," ",$array_address_shipping['firstname'])." ".str_replace($caratteri," ",$array_address_shipping['lastname'])) ,
                    'dati_spedizione_indirizzo' => preg_replace( "/\r|\n/", "",  str_replace( ';', '', $array_address_shipping['street']) ) ,
                    'dati_spedizione_email' => str_replace($caratteri," ",$array_address_shipping['email']) ?: $emailCliente,
                    'dati_spedizione_telefono' => str_replace($caratteri," ",$array_address_shipping['telephone']),

                    'dati_fatturazione_cliente' => str_replace($caratteri," ",$array_address_billing['firstname'])." ".str_replace($caratteri," ",$array_address_billing['lastname']) ,
                    'dati_fatturazione_indirizzo' =>  preg_replace( "/\r|\n/", "", str_replace( ';', '', $array_address_billing['street'].' '.$array_address_billing['city']. ' '.$array_address_billing['postcode']. ' '.$array_address_billing['region']. ' '.$array_address_billing['country_id']) ),
                    'dati_fatturazione_email' => str_replace($caratteri," ",$array_address_billing['email']) ?: $emailCliente,
                    'dati_fatturazione_telefono' => str_replace($caratteri," ",$array_address_billing['telephone']),

                    'spese_spedizione' => $spese_spedizione,
                    'regalo' => implode( '|', $array_regalo ),

                    'status' => $array_order_data['status'],
                    'metodo_pagamento' => $payment_method['method'],
                    'totale_pagato' => number_format($payment_method['base_amount_ordered'], 2 , '.', ''),
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
                    'marchio'   => $marchio,
                    'color'   => $color,
                    'size'   => $size,
                    'coupon_value' => $array_order_data['discount_amount'],
                    'referenza_fornitore' => $productConf,
                    'ItmsGrpCod' => 'none',
                    'ItmsGrpNam' => 'none',
                    'stagione' => $stagione
                ];

                if($item->getQtyOrdered()>1) {

                    $order_items[] = array(
                        'idOrder'   => $id,
                        //'idt'   => $order->getId(),
                        'idIncrement'   => $order->getIncrementId(),
                        //'idProduct'     => $item->getId(),
                        'idProduct'     => $productPId,
                        'name'          => str_replace($caratteri," ", $item->getName() ),
                        'sku'           => $productPsku,
                        'price'         => $productPrice,
                        'canale'        => $canale,
                        'stagione'      => $stagione,
                        'codice_ean'    => $codice_ean,
                        'marchio'       => $marchio,
                        'color'       => $color,
                        'size'       => $size,
                        'finalPriceInclTax'    => $item->getData('base_price_incl_tax'),
                        'originalPrice' => $originalPrice,
                        'taxAmount' => $item->getData('tax_amount'),
                        'orderedQty'   => 1
                    );

                } else {

                    $order_items[] = array(
                        'idOrder'   => $id,
                        //'idt'   => $order->getId(),
                        'idIncrement'   => $order->getIncrementId(),
                        //'idProduct'     => $item->getId(),
                        'idProduct'     => $productPId,
                        'name'          => str_replace($caratteri," ", $item->getName() ),
                        'sku'           => $productPsku,
                        'price'         => $productPrice,
                        'canale'        => $canale,
                        'stagione'      => $stagione,
                        'codice_ean'    => $codice_ean,
                        'marchio'       => $marchio,
                        'color'       => $color,
                        'size'       => $size,
                        'finalPriceInclTax'    => $item->getData('base_price_incl_tax'),
                        'originalPrice' => $originalPrice,
                        'taxAmount' => $item->getData('tax_amount'),
                        'orderedQty'   => $item->getQtyOrdered(),
                    );

                }


                $line++;
            }




            $array_dati_ordine[] = [
                'products' =>  json_encode($order_items),
                'entity_id' => $array_order_data['entity_id'],
                'order_id' => $array_order_data['increment_id'],
                'cliente' => str_replace($caratteri," ",$array_order_data['customer_firstname']).' '.str_replace($caratteri," ",$array_order_data['customer_lastname']),
                'cliente_nome' => str_replace($caratteri," ",$array_order_data['customer_firstname']),
                'cliente_cognome' => str_replace( $caratteri," ",$array_order_data['customer_lastname']),
                'data_ordine' => $array_order_data['created_at'],
                'dati_spedizione_cliente' => str_replace($caratteri," ",$array_address_shipping['firstname'])." ".str_replace($caratteri," ",$array_address_shipping['lastname']),
                'dati_spedizione_indirizzo' => str_replace($caratteri," ",$array_address_shipping['street'])." <br>".str_replace($caratteri," ",$array_address_shipping['city'])." <br>".str_replace($caratteri," ",$array_address_shipping['postcode'])." <br>".str_replace($caratteri," ",$array_address_shipping['region'])." ".str_replace($caratteri," ",$array_address_shipping['country_id']),
                'dati_spedizione_email' => $emailCliente,
                'dati_spedizione_telefono' => str_replace($caratteri," ",$array_address_shipping['telephone']),

                //,   'dati_fatturazione' => $array_dati_fatturazione = json_encode(array(
                'dati_fatturazione_cliente' => str_replace($caratteri," ",$array_address_billing['firstname'])." ".str_replace($caratteri," ",$array_address_billing['lastname']),
                'dati_fatturazione_indirizzo' => str_replace($caratteri," ",$array_address_billing['street'])." ".str_replace($caratteri," ",$array_address_billing['city'])." ".str_replace($caratteri," ",$array_address_billing['postcode'])." ".str_replace($caratteri," ",$array_address_billing['region'])." ".str_replace($caratteri," ",$array_address_billing['country_id']),
                'dati_fatturazione_email' => str_replace($caratteri," ",$array_address_billing['email']),
                'dati_fatturazione_telefono' => str_replace($caratteri," ",$array_address_billing['telephone']),
                //    ))
                'spese_spedizione' => $spese_spedizione,
                'regalo' => json_encode($array_regalo),

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
            ];


        }

        array_unshift($array_items_order, array_keys($array_items_order[0]));
        array_unshift($array_dati_ordine, array_keys($array_dati_ordine[0]));

        $directory = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_EXPORT);

        //SAP
        $filename_items_orders = $directory. "/sap/" . date('Ymd_His').'_items_orders_export.csv';
        $this->csv->setDelimiter(';')->appendData($filename_items_orders, $array_items_order);

        //MIDDLEWARE
        $filename_orders = $directory. "/mw/" . date('Ymd_His').'_orders_export.csv';
        $this->csv->setDelimiter(';')->appendData($filename_orders, $array_dati_ordine);


    }

}
