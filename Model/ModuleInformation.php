<?php

namespace ScaliaGroup\Integration\Model;

use ScaliaGroup\Integration\Api\CacheManagementInterface;
use ScaliaGroup\Integration\Api\ModuleInformationInterface;
use ScaliaGroup\Integration\Helper\Config;
use ScaliaGroup\Integration\Logger\Logger;

class ModuleInformation implements ModuleInformationInterface
{
    protected $config;
    protected $logger;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        Logger $logger,
        Config $config,
        \Magento\Framework\Module\Dir $moduleDir,
        \Magento\Framework\Filesystem\Driver\File $driverFile,
        \ScaliaGroup\Integration\Api\Data\ModuleInterfaceFactory $moduleInterfaceFactory
    ) {

        $this->logger = $logger;
        $this->config = $config;
        $this->moduleDir = $moduleDir;
        $this->driverFile = $driverFile;
        $this->moduleFactory = $moduleInterfaceFactory;
    }

    public function get()
    {
        $path = $this->moduleDir->getDir('ScaliaGroup_Integration');

        if($this->driverFile->isExists($path . "/VERSION"))
            $version = $this->driverFile->fileGetContents($path . "/VERSION");
        else
            $version = "????";
        
        return $this->moduleFactory->create([
            'data' => [
                'path' => $path,
                'version' => $version
            ]
        ]);
    }
}