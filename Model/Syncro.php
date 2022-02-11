<?php

namespace ScaliaGroup\Integration\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Webapi\Rest\Request;
use ScaliaGroup\Integration\Api\SyncroInterface;

class Syncro implements SyncroInterface
{

    private $request;
    protected $productFactory;
    protected $productRepository;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->productFactory = ObjectManager::getInstance()->create(ProductFactory::class);
        $this->productCollection = ObjectManager::getInstance()->create(Collection::class);
        $this->productRepository = ObjectManager::getInstance()->create(ProductRepository::class);
    }

    public function post()
    {
        $body = $this->request->getBodyParams();

        $products = $body['products'] ?? [];

        foreach($products as $product) {

            foreach($product['variations'] as $simpleProduct) {

                try {


                    echo $simpleProduct['sku'] . "\n";

                    /** @var Product $product */
                    $product = $this->productFactory->create()->loadByAttribute('sku', $sku = $simpleProduct['sku']);
                    if(!$product) {
                        $product = $this->productFactory->create();

                        $product->setSku($sku);
                    }
                    $product->setName($product['name']);
                    echo "Magento SKU: " . $product->getSku() . "\n";
                    echo "StoreID: " . $product->getStoreId() . "\n";



                } catch (\Throwable $e) {
                    echo $e->getMessage() ."\n";
                    exit;
                }


            }




        }





        exit;
    }
}
