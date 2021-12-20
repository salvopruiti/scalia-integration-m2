<?php

namespace ScaliaGroup\Integration\Model;

use Magento\Framework\Model\AbstractModel;

class Module extends AbstractModel implements \ScaliaGroup\Integration\Api\Data\ModuleInterface
{
    public function getVersion()
    {
        return $this->_getData('version');
    }
}