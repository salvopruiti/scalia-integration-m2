<?php

namespace ScaliaGroup\Integration\Console\Commands;

use Laminas\Http\Client;
use Magento\Framework\App\ObjectManager;
use ScaliaGroup\Integration\Helper\Config;
use ScaliaGroup\Integration\Logger\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ColorsGroupsSyncCommand extends Command
{

    protected $connection;
    private $logger;
    private $config;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        Config $config,
        Logger $logger,
        string $name = null
    )
    {
        parent::__construct($name);

        $this->connection = $resource->getConnection();
        $this->logger = $logger;
        $this->config = $config;
    }



    protected function configure()
    {
        $this->setName('sg:colors:groups:sync');
        $this->setDescription("Sync Color Groups with Middleware");
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = new Client();

        $host = $this->config->getMiddlewareHost();
        $access_token = $this->config->getMiddlewareAccessToken();


       try {

           $response = $client->setUri("$host/api/v1/color-groups")
               ->setHeaders([
                   'Accept' => 'application/json',
                   'Authorization' => "Bearer $access_token"
               ])
               ->send();

           if($response->getStatusCode() != 200) {
               $this->logger->error('ColorGroupsSyncCommand', [
                   'status' => $response->getStatusCode(),
                   'phrase' => $response->getReasonPhrase(),
                   'body' => $response->getBody()
               ]);
               die("Invalid Response from Middleware! See sg_integration log");
           }

       } catch (\Exception $e) {

           $this->logger->error('ColorGroupsSyncCommand', [
               'code' => $e->getCode(),
               'message' => $e->getMessage()
           ]);
           die("Invalid Response from Middleware! See sg_integration log");

       }

        if(!class_exists('\Amasty\GroupedOptions\Model\GroupAttrFactory')) {
            $this->logger->error('ColorGroupsSyncCommand', [
                'code' => 1,
                'description' => 'Missing Required Module',
                'params' => [
                    'module' => 'Amasty_GroupedOptions'
                ]
            ]);
            die("Il Modulo Amasty GroupedOptions non è presente!");
        }

        $groupAttrFactory = ObjectManager::getInstance()->get('\Amasty\GroupedOptions\Model\GroupAttrFactory');

        $color_attribute_id = $this->getColorAttributeId();
        $available_colors = $this->getAvailableColors($color_attribute_id);

        $groups = json_decode($response->getBody(), true);

        foreach($groups as $group) {

            echo $group['name']['it']   ;

            //recupero l'elenco dei colori da associare a quel gruppo
            $group_colors = [];
            foreach($group['colors'] as $color) {
                if(isset($available_colors[mb_strtoupper($color)]))
                    $group_colors[/*mb_strtoupper($color)*/] = $available_colors[mb_strtoupper($color)];
            }

            $newName = json_encode([
                $group['name']['it'], $group['name']['it'], $group['name']['en'] ?? $group['name']['it'], $group['name']['de_DE'] ?? $group['name']['en'] ?? $group['name']['it']
            ]);

            /** @var GroupAttr $existingGroup */
            $existingGroup = $groupAttrFactory->create()->load($group['code'], 'group_code');
            if(!$existingGroup->getId()) {
                //Creo
                echo "\nIl Gruppo non esiste!\n\n";
                continue;

            } else {
                //Aggiorno

                if($group['value_picker']) {
                    $existingGroup->setVisual($group['value_picker']);
                }
                $existingGroup->setName($newName);
                $existingGroup->save();

            }

            $colori_associati = $existingGroup->getData('attribute_options');

            $to_remove = array_diff($colori_associati, $group_colors);
            $to_add = array_diff($group_colors, $colori_associati);


            if($to_remove) {

                $removed = $this->connection->delete('amasty_grouped_options_group_option',[
                    $this->connection->quoteInto("group_id = ?", [$existingGroup->getId()]),
                    $this->connection->quoteInto("option_id IN (?)", [$to_remove]),
                ]);

                echo " - Eliminati: $removed colori";

            }

            if($to_add) {

                $rows = [];
                foreach($to_add as $item) {
                    $rows[] = [
                        'group_id' => $existingGroup->getId(),
                        'option_id' => $item,
                        'sort_order' => 0
                    ];
                }

                $added = $this->connection->insertArray('amasty_grouped_options_group_option', ['group_id', 'option_id', 'sort_order'], $rows);

                echo " - Aggiunti: $added colori";


            }

            if(!$to_remove && !$to_add)
                echo " - Nessun Aggiornamento";

            echo "\n";

        }


    }

    protected function getColorAttributeId()
    {
        $tbl_eav_attribute = $this->connection->getTableName("eav_attribute");

        return $this->connection->fetchOne($this->connection->select()
            ->from($tbl_eav_attribute)
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns('attribute_id')
            ->where('attribute_code = ?', 'color'));
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

        foreach($this->connection->fetchAll($query) as $color) {
            $colors[mb_strtoupper($color['name'])] = $color['id'];
        }

        return $colors;
    }


}
