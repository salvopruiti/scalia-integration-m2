<?php

namespace ScaliaGroup\Integration\Observer;

use Laminas\Http\Client;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use ScaliaGroup\Integration\Helper\Config;
use ScaliaGroup\Integration\Logger\Logger;

class CategorySaveAfter implements ObserverInterface {

    protected $logger;
    private $config;

    public function __construct(Logger $logger, Config $config)
    {
        $this->logger = $logger;
        $this->config = $config;
    }

    public function execute(Observer $observer)
    {
        if(!$this->config->getEnabled() || $this->config->getExportCategoriesEnabled())
            return;

        $category = $observer->getEvent()->getCategory();

        if($this->config->getIsDebugMode())
            $this->logger->debug("CategorySaveAfter", ['category' => $category->toArray()]);

        try {
            $host = $this->config->getMiddlewareHost();
            $access_token = $this->config->getMiddlewareAccessToken();

            $client = new Client($host . "/api/v1/categories");
            $client->setHeaders([
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $access_token
            ]);
            $client->setMethod('GET');
            $client->send();

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
