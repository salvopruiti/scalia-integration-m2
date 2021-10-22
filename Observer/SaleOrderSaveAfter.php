<?php

namespace ScaliaGroup\Integration\Observer;

use Magento\Framework\Event\ObserverInterface;
use ScaliaGroup\Integration\Logger\Logger;
use Laminas\Http\Client;

class SaleOrderSaveAfter implements ObserverInterface
{

    protected $logger;
    protected $scopeConfig;

    public function __construct(Logger $logger, \ScaliaGroup\Integration\Helper\Config $config)
    {
        $this->logger = $logger;
        $this->config = $config;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $module_enabled = $this->config->getEnabled();
        $export_orders_enabled = $this->config->getExportOrdersEnabled();
        if (!$module_enabled || !$export_orders_enabled)
            return;

        try {
            $host = $this->config->getMiddlewareHost();
            $access_token = $this->config->getMiddlewareAccessToken();

            $client = new Client($host . "/api/v1/orders");

            $this->logger->debug("OrderSave", ['url' => $client->getUri()->toString()]);

            $client->setHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $access_token
            ]);

            $order = $observer->getEvent()->getOrder();

            $client->setMethod("post");
            $client->setParameterGet($parameters = [
                'type' => 'order',
                'entity_id' => $order->getId()
            ]);

            $response = $client->send();

            if($response->getStatusCode() != 200 && $this->config->getIsDebugMode()) {
                $this->logger->debug("SaleOrderSaveAfter", [
                    'url' => $client->getUri()->toString(),
                    'status' => $response->getStatusCode(),
                    'status_txt' => $response->getReasonPhrase(),
                    'body' => json_decode($response->getBody(), true),
                ]);
            }

        } catch (\Exception $e) {

            $this->logger->error($e->getMessage());

        }

    }
}
