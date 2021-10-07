<?php

namespace ScaliaGroup\Integration\Console\Commands;

use Laminas\Http\Client;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem;
use Magento\Swatches\Helper\Media;
use Magento\Swatches\Model\ResourceModel\Swatch\Collection;
use ScaliaGroup\Integration\Helper\Config;
use ScaliaGroup\Integration\Logger\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ColorsSyncCommand extends Command
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
        $this->setName('sg:colors:sync');
        $this->setDescription("Sync Colors with Middleware");
        $this->addOption('force', 'f');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(Area::AREA_ADMINHTML);
        $client = new Client();

        $host = $this->config->getMiddlewareHost();
        $access_token = $this->config->getMiddlewareAccessToken();

        $timestamp = $this->config->getUpdateTimestamp('colors');
        $color_attribute = $this->getColorAttribute();
        $available_colors = $color_attribute->getOptions();

        try {

            $client->setUri("$host/api/v1/colors")
                ->setHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => "Bearer $access_token"
                ]);

            if($timestamp && !$input->getOption('force'))
                $client->setParameterGet([
                    'timestamp' => $timestamp
                ]);

            $response = $client->send();

            if ($response->getStatusCode() != 200) {
                $this->logger->error('ColorsSyncCommand', [
                    'status' => $response->getStatusCode(),
                    'phrase' => $response->getReasonPhrase(),
                    'body' => $response->getBody()
                ]);
                die("Invalid Response from Middleware! See sg_integration log");
            }

        } catch (\Exception $e) {

            $this->logger->error('ColorsSyncCommand', [
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ]);
            die("Invalid Response from Middleware! See sg_integration log");

        }

        $response = json_decode($response->getBody(), true);
        $colors = $response['data'];
        $timestamp = $response['timestamp'];

        $filesystem = ObjectManager::getInstance()->get(Filesystem::class);
        $swatchHelper = ObjectManager::getInstance()->get(Media::class);
        $productMediaConfig = ObjectManager::getInstance()->get(Product\Media\Config::class);

        $swatchCollection = ObjectManager::getInstance()->get(Collection::class);

        $mediaDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
//        dd($productMediaConfig->getBaseTmpMediaPath(), $mediaDirectory->getAbsolutePath($productMediaConfig->getBaseTmpMediaPath()));


        $output->writeln("Colori da aggiornare: " . $response['total']);
        foreach ($colors as $color) {

            $current_color = array_filter($available_colors, function($acolor) use($color) {
                return mb_strtoupper($acolor->getLabel()) == $color['name'];
            });

            $current_color = array_first($current_color);

            //verifico se esiste il colore
            if(!isset($available_colors[mb_strtoupper($color['name'])])) {
                $output->writeln(sprintf("Colore non trovato su Magento: %s", $color['name']));
                continue;
            }

            $option_id = $available_colors[mb_strtoupper($color['name'])];
            if($color['color_picker']) {

            } else {

            }



        }

        $this->config->saveUpdateTimestamp('colors', $timestamp);
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
