<?php

namespace ScaliaGroup\Integration\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use ScaliaGroup\Integration\Api\Data\OrderGiftMessageInterfaceFactory;
use ScaliaGroup\Integration\Api\Data\OrderInterface;
use ScaliaGroup\Integration\Api\Data\OrderInterfaceFactory;
use ScaliaGroup\Integration\Api\Data\OrderProductInterfaceFactory;
use ScaliaGroup\Integration\Api\OrderRepositoryInterface;
use ScaliaGroup\Integration\Helper\OrderExporter;

class OrderRepository implements OrderRepositoryInterface
{

    public function get($id)
    {
        if (!$id) {
            throw new InputException(__('An ID is needed. Set the ID and try again.'));
        }

        $this->dataFactory = ObjectManager::getInstance()->create(OrderInterfaceFactory::class);
        $this->_orderProductFactory = ObjectManager::getInstance()->create(OrderProductInterfaceFactory::class);
        $this->_orderGiftMessageFactory = ObjectManager::getInstance()->create(OrderGiftMessageInterfaceFactory::class);

        $order = ObjectManager::getInstance()->create(OrderExporter::class)->export($id);



        foreach($order['products'] as $product) {
            $products[] = $this->_orderProductFactory->create(['data' => $product]);
        }

        foreach($order['regalo'] as $message) {
            $regalo[] = $this->_orderGiftMessageFactory->create(['data' => $message]);
        }

        $order['products'] = $products;
        $order['regalo'] = $regalo;

        /** @var OrderInterface $page */
        $page = $this->dataFactory->create(['data' => $order]);

        return $page;


    }
}
