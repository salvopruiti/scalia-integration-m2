<?php

namespace ScaliaGroup\Integration\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem\DirectoryList;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONFIG_ENABLED_PATH = "sg_integration/general/enabled";
    const CONFIG_EXPORT_CATEGORIES_ENABLED_PATH = "sg_integration/general/export_categories_enabled";
    const CONFIG_EXPORT_ORDERS_ENABLED_PATH = "sg_integration/general/export_orders_enabled";
    const CONFIG_MIDDLEWARE_HOST = "sg_integration/middleware/host";
    const CONFIG_MIDDLEWARE_ACCESS_TOKEN = "sg_integration/middleware/access_token";
    const CONFIG_DEBUG_MODE = 'sg_integration/general/debug_mode';

    protected $directoryList;

    public function __construct(
        Context $context, DirectoryList $directoryList)
    {
        parent::__construct($context);
        $this->directoryList = $directoryList;
    }


    public function getIsDebugMode()
    {
        return $this->scopeConfig->isSetFlag(self::CONFIG_DEBUG_MODE);
    }

    public function getEnabled()
    {
        return (bool)$this->scopeConfig->getValue(self::CONFIG_ENABLED_PATH);
    }

    public function getExportOrdersEnabled()
    {
        return (bool)$this->scopeConfig->getValue(self::CONFIG_EXPORT_ORDERS_ENABLED_PATH);
    }

    public function getExportCategoriesEnabled()
    {
        return (bool)$this->scopeConfig->getValue(self::CONFIG_EXPORT_CATEGORIES_ENABLED_PATH);
    }

    public function getValue($path, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        return $this->scopeConfig->getValue($path, $scope, $scopeCode);
    }

    public function getMiddlewareHost()
    {
        $host =  $this->getValue(self::CONFIG_MIDDLEWARE_HOST);
        if(strpos($host, 'http') === false) {
            $host = "http://" . $host;
        }
        return $host;
    }

    public function getMiddlewareAccessToken()
    {
        return $this->getValue(self::CONFIG_MIDDLEWARE_ACCESS_TOKEN);
    }

    public function getUpdateTimestamp($type)
    {
        $filepath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR) . "sgi_timestamp_$type";
        if(!is_file($filepath))
            return null;
        else
            return file_get_contents($filepath);
    }

    public function saveUpdateTimestamp($type, $timestamp = null)
    {
        $filepath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR) . "sgi_timestamp_$type";
        if(!$timestamp)
            @unlink($filepath);
        else
            file_put_contents($filepath, $timestamp);
    }
}
