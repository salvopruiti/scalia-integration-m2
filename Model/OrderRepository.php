<?php

namespace ScaliaGroup\Integration\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use ScaliaGroup\Integration\Api\Data\OrderInterface;
use ScaliaGroup\Integration\Api\Data\OrderInterfaceFactory;
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

        $order = ObjectManager::getInstance()->create(OrderExporter::class)->export($id);

        /** @var OrderInterface $page */
        $page = $this->dataFactory->create();

        foreach($order as $key => $value) {
            $page->setData($key, $value);
        }

        return $page;


    }
}
