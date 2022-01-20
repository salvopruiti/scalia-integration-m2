<?php

namespace ScaliaGroup\Integration\Model;

use Magento\Framework\Webapi\Rest\Request;
use ScaliaGroup\Integration\Api\SyncroInterface;

class Syncro implements SyncroInterface
{

    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function post()
    {
        $body = $this->request->getBodyParams();

        $products = $body['products'] ?? [];

        echo json_encode($products, 128);




        exit;
    }
}