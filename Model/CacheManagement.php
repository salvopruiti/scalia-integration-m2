<?php

namespace ScaliaGroup\Integration\Model;

use ScaliaGroup\Integration\Api\CacheManagementInterface;
use ScaliaGroup\Integration\Helper\Config;
use ScaliaGroup\Integration\Logger\Logger;

class CacheManagement implements CacheManagementInterface
{
    protected $_cacheTypeList;
    protected $config;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        Logger $logger,
        Config $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
    ) {

        $this->_cacheTypeList = $cacheTypeList;
        $this->logger = $logger;
        $this->config = $config;
    }

    public function get()
    {
        $types = [];
        $invalid = array_keys($this->_cacheTypeList->getInvalidated());
        foreach($this->_cacheTypeList->getTypes() as $type) {
            $types[$type->getId()] = [
                'id' => $type->getId(),
                'name' => $type->getCacheType(),
                'valid' => !in_array($type->getId(), $invalid)
            ];
        }

        return $types;

    }

    public function clean()
    {
        foreach($this->_cacheTypeList->getTypes() as $type) {
            if($this->config->getIsDebugMode()) $this->logger->debug("CacheClean", ['type' => $type->getId()]);
            $this->_cacheTypeList->cleanType($type->getId());
        };
    }
}