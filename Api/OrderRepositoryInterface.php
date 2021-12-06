<?php

namespace ScaliaGroup\Integration\Api;

use ScaliaGroup\Integration\Api\Data\OrderInterface;

/**
 * @api
 */
interface OrderRepositoryInterface
{
    /**
     * Loads a specified order.
     *
     * @param int $id The order ID.
     * @return \ScaliaGroup\Integration\Api\Data\OrderInterface;
     */
    public function get($id);
}
