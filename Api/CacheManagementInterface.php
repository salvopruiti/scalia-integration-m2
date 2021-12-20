<?php

namespace ScaliaGroup\Integration\Api;

/**
 * @api
 */
interface CacheManagementInterface
{
    /**
     * Loads a specified order.
     *
     * @api
     * @return array;
     */
    public function get();


    /**
     * @api
     * @return mixed
     */
    public function clean();
}
