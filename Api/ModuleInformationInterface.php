<?php

namespace ScaliaGroup\Integration\Api;

/**
 * @api
 */
interface ModuleInformationInterface
{
    /**
     * Loads a specified order.
     *
     * @api
     * @return \ScaliaGroup\Integration\Api\Data\ModuleInterface;
     */
    public function get();

}
