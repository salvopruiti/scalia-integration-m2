<?php

namespace ScaliaGroup\Integration\Model;

use Magento\Framework\Model\AbstractModel;
use ScaliaGroup\Integration\Api\Data\OrderGiftMessageInterface;
use ScaliaGroup\Integration\Api\Data\OrderInterface;

class OrderGiftMessage extends AbstractModel implements OrderGiftMessageInterface
{

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function getGiftId()
    {
        return $this->_getData('gift_id');
    }

    public function setGiftId($gift_id)
    {
        return $this->setData('gift_id', $gift_id);
    }

    public function getMittente()
    {
        return $this->_getData('mittente');
    }

    public function setMittente($mittente)
    {
        return $this->setData('mittente', $mittente);
    }

    public function getDestinatario()
    {
        return $this->_getData('destinatario');
    }

    public function setDestinatario($destinatario)
    {
        return $this->setData('destinatario', $destinatario);
    }

    public function getMessaggio()
    {
        return $this->_getData('messaggio');
    }

    public function setMessaggio($messaggio)
    {
        return $this->setData('messaggio', $messaggio);
    }
}
