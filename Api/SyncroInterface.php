<?php

namespace ScaliaGroup\Integration\Api;

use ScaliaGroup\Integration\Api\Data\OrderInterface;

/**
 * @api
 */
interface SyncroInterface
{
    /**
     * Loads a specified order.
     *
     * @return mixed
     */
    public function post();
}
