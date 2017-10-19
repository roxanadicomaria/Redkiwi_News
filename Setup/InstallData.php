<?php
namespace Redkiwi\News\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface {
    
    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        $setup->startSetup();
 
        $tableName = \Redkiwi\News\Setup\InstallSchema::NEWS_ITEMS_TABLE;
        $table = $setup->getTable($tableName);
        $content = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p><p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem.</p><p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga.</p>';
        $shortContent = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>';        
        $data = [
            [
                'url_key' => 'lorem-ipsum', 
                'title' => 'Lorem ipsum', 
                'content' => $content, 
                'short_content' => $shortContent, 
                'author' => 'Cicero', 
                'meta_description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit',
                'meta_keywords' => 'Lorem, ipsum, news',
                'publish_at' => '2017-04-03 00:00:00',
                'status' => 1
            ],
            [
                'url_key' => 'dolor', 
                'title' => 'Dolor', 
                'content' => $content, 
                'short_content' => $shortContent, 
                'author' => 'Petru Iuga', 
                'meta_description' => 'Dolor, consectetur adipiscing elit',
                'meta_keywords' => 'dolor, news',
                'publish_at' => '2017-02-22 00:00:00',
                'status' => 1
            ],
            [
                'url_key' => 'at-vero-eos', 
                'title' => 'At vero eos', 
                'content' => $content, 
                'short_content' => $shortContent, 
                'author' => 'Cicero',
                'meta_description' => 'At vero eos, consectetur adipiscing elit',
                'meta_keywords' => 'At vero, eos, news',
                'publish_at' => '2017-04-17 00:00:00',
                'status' => 1
            ],
            [
                'url_key' => 'laborum', 
                'title' => 'Laborum', 
                'content' => $content, 
                'short_content' => $shortContent, 
                'author' => 'John', 
                'meta_description' => 'Laborum, consectetur adipiscing elit',
                'meta_keywords' => 'laborum, news',
                'publish_at' => '2018-05-22 00:00:00',
                'status' => 1
            ],
            [
                'url_key' => 'dolorum-fuga', 
                'title' => 'Dolorum Fuga', 
                'content' => $content, 
                'short_content' => $shortContent, 
                'author' => 'Cicero',
                'meta_description' => 'Dolorum Fuga, consectetur adipiscing elit',
                'meta_keywords' => 'Dolorum, Fuga, news',
                'publish_at' => '2017-04-17 00:00:00',
                'status' => 0
            ]
        ];
        
        // insert multiple rows
        $setup->getConnection()->insertMultiple($table, $data);
        
        // insert single row
        $item = [
            'url_key' => 'consectetur', 
            'title' => 'Consectetur', 
            'content' => $content, 
            'short_content' => $shortContent, 
            'author' => 'Petru'
        ];
        $setup->getConnection()->insert($table, $item);
 
        $setup->endSetup();
    }
    
}
