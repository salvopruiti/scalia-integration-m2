<?php

namespace ScaliaGroup\Integration\Console\Commands;

use Laminas\Http\Client;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Sales\Model\Order;
use Magento\Swatches\Helper\Media;
use Magento\Swatches\Model\ResourceModel\Swatch\Collection;
use ScaliaGroup\Integration\Helper\Config;
use ScaliaGroup\Integration\Logger\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OrdersExportCommand extends Command
{

    protected $connection;
    private $logger;
    private $config;
    private $eavConfig;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        Config                                    $config,
        Logger                                    $logger,
        \Magento\Eav\Model\Config                 $eavConfig,
        string                                    $name = null
    )
    {
        parent::__construct($name);

        $this->connection = $resource->getConnection();
        $this->logger = $logger;
        $this->config = $config;
        $this->eavConfig = $eavConfig;
        $this->state = ObjectManager::getInstance()->get(State::class);
    }


    protected function configure()
    {
        $this->setName('sg:orders:export');
        $this->setDescription("Export Orders To Middleware");
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $host = $this->config->getMiddlewareHost();
        $access_token = $this->config->getMiddlewareAccessToken();


        $salesOrderCollection = ObjectManager::getInstance()->create(\Magento\Sales\Model\ResourceModel\Order\Collection::class);

        $salesOrderCollection->addFieldToFilter('created_at', ['gteq' => (new \DateTime())->modify('- 1 day')]);

        /** @var Order $order */
        foreach ($salesOrderCollection as $order) {

            try {

                if($this->config->getIsDebugMode())
                    $this->logger->debug("Invio Ordine", ['order' => $order->toArray()]);


                $output->write('Invio Ordine '. $order->getIncrementId() .'... ');

                $client = new Client();
                $client->setUri("$host/api/v1/orders")
                    ->setHeaders([
                        'Accept' => 'application/json',
                        'Authorization' => "Bearer $access_token"
                    ]);

                $client->setMethod('post');
                $client->setRawBody($order->toJson());

                $response = $client->send();

                if ($response->getStatusCode() != 200) {
                    $this->logger->error('ColorsSyncCommand', [
                        'status' => $response->getStatusCode(),
                        'phrase' => $response->getReasonPhrase(),
                        'body' => $response->getBody()
                    ]);
                    die("Invalid Response from Middleware! See sg_integration log");
                }

                $output->writeln('<info>OK</info>');

            } catch (\Exception $e) {

                $this->logger->error('ColorsSyncCommand', [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]);
                die("Invalid Response from Middleware! See sg_integration log");

            }
        }





    }

    protected function getColorAttribute()
    {
        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, 'color');
        return $attribute;
    }

    protected function getAvailableColors($attribute_id)
    {
        $query = $this->connection->select()
            ->from(['eav_attribute_option'], 'option_id as id')
            ->joinLeft('eav_attribute_option_value', 'eav_attribute_option.option_id = eav_attribute_option_value.option_id',
                'value as name'
            )
//            ->reset(\Zend_Db_Select::COLUMNS)
//            ->columns('eav_attribute_option_value.option_id, eav_attribute_option.value as color_name')
            ->where('eav_attribute_option.attribute_id = ?', [$attribute_id]);

        $colors = [];

        foreach ($this->connection->fetchAll($query) as $color) {
            $colors[mb_strtoupper($color['name'])] = $color['id'];
        }

        return $colors;
    }


    public function generateOptions($values, $swatchType = 'visual')
    {
        $i = 0;
        foreach($values as $key => $value) {
            $order["option_{$i}"] = $i;
            $optionsStore["option_{$i}"] = array(
                0 => $key, // admin
                1 => $value['label'], // default store view
            );
            $textSwatch["option_{$i}"] = array(
                1 => $value['label'],
            );
            $visualSwatch["option_{$i}"] = $value['url'];
            $delete["option_{$i}"] = '';
            $i++;
        }

        switch($swatchType)
        {
            case 'text':
                return [
                    'optiontext' => [
                        'order'     => $order,
                        'value'     => $optionsStore,
                        'delete'    => $delete,
                    ],
                    'swatchtext' => [
                        'value'     => $textSwatch,
                    ],
                ];
                break;
            case 'visual':
                return [
                    'optionvisual' => [
                        'order'     => $order,
                        'value'     => $optionsStore,
                        'delete'    => $delete,
                    ],
                    'swatchvisual' => [
                        'value'     => $visualSwatch,
                    ],
                ];
                break;
            default:
                return [
                    'option' => [
                        'order'     => $order,
                        'value'     => $optionsStore,
                        'delete'    => $delete,
                    ],
                ];
        }
    }


}
