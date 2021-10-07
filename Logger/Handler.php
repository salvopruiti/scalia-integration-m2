<?php

namespace ScaliaGroup\Integration\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    protected $loggerType = Logger::DEBUG;

    protected $fileName = "/var/log/sg_integration.log";
}
