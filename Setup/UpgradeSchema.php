<?php
namespace Redkiwi\News\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class UpgradeSchema implements UpgradeSchemaInterface {
    
    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $setup->startSetup();
        
        if (version_compare($context->getVersion(), '1.1.0') < 0) {
            // Get table name
            $tableName = \Redkiwi\News\Setup\InstallSchema::NEWS_ITEMS_TABLE;
            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                // add image column
                $setup->getConnection()->addColumn(
                    $setup->getTable($tableName),
                    'image',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => '255',
                        'nullable' => true,
                        'comment' => 'Image'
                    ]
                );
                
            }
        }
        
        if (version_compare($context->getVersion(), '1.3.0') < 0) {
            // Get table name
            $tableName = \Redkiwi\News\Setup\InstallSchema::NEWS_ITEMS_TABLE;
            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                // add image column
                $setup->getConnection()->addColumn(
                    $setup->getTable($tableName),
                    'file',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => '255',
                        'nullable' => true,
                        'comment' => 'File'
                    ]
                );
                
            }
        }
        
        // add categories table
        if (version_compare($context->getVersion(), '1.4.0') < 0) {
            // Get table name
            $tableName = \Redkiwi\News\Setup\InstallSchema::NEWS_CATEGORIES_TABLE;
            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableName) != true) {
                $tableName = $setup->getTable($tableName);
                $table = $setup->getConnection()->newTable($tableName);
                $table->addColumn(
                        'id',
                        Table::TYPE_INTEGER,
                        null,
                        ['identity'  => true, 'unsigned'  => true, 'nullable'  => false, 'primary'   => true],
                        'News Category ID'
                    )
                    ->addColumn(
                        'url_key', 
                        Table::TYPE_TEXT, 
                        255,
                        ['nullable' => false, 'default' => ''],
                        'URL Key'
                    )
                    ->addColumn(
                        'title', 
                        Table::TYPE_TEXT, 
                        255,
                        ['nullable' => false, 'default' => ''],
                        'Title'
                    )
                    ->addColumn(
                        'content', 
                        Table::TYPE_TEXT, 
                        '1M',
                        ['nullable' => true],
                        'Content'
                    )
                    ->addColumn(
                        'created_at',
                        Table::TYPE_TIMESTAMP,
                        null,
                        ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                        'Created At'
                    )
                    ->addColumn(
                        'updated_at',
                        Table::TYPE_TIMESTAMP,
                        null,
                        ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                        'Updated At'
                    )
                    ->addColumn(
                        'stores', 
                        Table::TYPE_TEXT, 
                        255,
                        ['nullable' => false, 'default' => '0'],
                        'Display on Stores'
                    )
                    ->addColumn(
                        'status', 
                        Table::TYPE_BOOLEAN, 
                        null, 
                        ['unsigned' => true, 'nullable' => false, 'default'   => '1'], 
                        'Status'
                    )
                    ->addColumn(
                        'position', 
                        Table::TYPE_INTEGER, 
                        null, 
                        ['unsigned' => true, 'nullable' => false, 'default'   => '0'], 
                        'Position'
                    )
                    ->addColumn(
                        'meta_description', 
                        Table::TYPE_TEXT, 
                        '64k',
                        ['nullable' => false, 'default' => ''],
                        'Meta Description'
                    )
                    ->addColumn(
                        'meta_keywords', 
                        Table::TYPE_TEXT, 
                        512,
                        ['nullable' => false, 'default' => ''],
                        'Meta Keywords'
                    )
                    ->addIndex(
                        $setup->getIdxName($tableName, ['url_key'], AdapterInterface::INDEX_TYPE_INDEX),
                        ['url_key'],
                        ['type' => AdapterInterface::INDEX_TYPE_INDEX]
                    )
                    ->addIndex(
                        $setup->getIdxName($tableName, ['title', 'content'], AdapterInterface::INDEX_TYPE_FULLTEXT),
                        ['title', 'content'],
                        ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
                    )
                    ->setComment('News Categories');            
                $setup->getConnection()->createTable($table);                
            }
        }
        
         // add categories-news items associative table
        if (version_compare($context->getVersion(), '1.5.0') < 0) {
            // Get table name
            $tableName = \Redkiwi\News\Setup\InstallSchema::NEWS_CATEGORIES_ITEMS_TABLE;
            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableName) != true) {
                $tableName = $setup->getTable($tableName);
                $table = $setup->getConnection()->newTable($tableName);
                $table->addColumn(
                       'id',
                       Table::TYPE_INTEGER,
                       null,
                       ['identity' => true, 'nullable' => false, 'primary' => true],
                       'ID'
                    )->addColumn(
                        'category_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
                        'Category ID'
                    )->addColumn(
                        'news_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => false, 'primary' => true, 'default' => '0'],
                        'News ID'
                    )->addColumn(
                        'position',
                        Table::TYPE_INTEGER,
                        null,
                        ['nullable' => false, 'default' => '0'],
                        'Position'
                    )->addIndex(
                        $setup->getIdxName(\Redkiwi\News\Setup\InstallSchema::NEWS_CATEGORIES_ITEMS_TABLE, ['news_id']),
                        ['news_id']
                    )->addIndex(
                        $setup->getIdxName(
                            \Redkiwi\News\Setup\InstallSchema::NEWS_CATEGORIES_ITEMS_TABLE,
                            ['category_id', 'news_id'],
                            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                        ),
                        ['category_id', 'news_id'],
                        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                    )->addForeignKey(
                        $setup->getFkName(
                            \Redkiwi\News\Setup\InstallSchema::NEWS_CATEGORIES_ITEMS_TABLE, 
                            'news_id', 
                            \Redkiwi\News\Setup\InstallSchema::NEWS_ITEMS_TABLE, 
                            'id'
                        ),
                        'news_id',
                        $setup->getTable(\Redkiwi\News\Setup\InstallSchema::NEWS_ITEMS_TABLE),
                        'id',
                        Table::ACTION_CASCADE
                    )->addForeignKey(
                        $setup->getFkName(
                            \Redkiwi\News\Setup\InstallSchema::NEWS_CATEGORIES_ITEMS_TABLE, 
                            'category_id', 
                            \Redkiwi\News\Setup\InstallSchema::NEWS_CATEGORIES_TABLE, 
                            'id'
                        ),
                        'category_id',
                        $setup->getTable(\Redkiwi\News\Setup\InstallSchema::NEWS_CATEGORIES_TABLE),
                        'id',
                        Table::ACTION_CASCADE
                    )
                    ->setComment('News Items To Category Linkage Table');
                $setup->getConnection()->createTable($table);
            }
        }

        // add gallery field to news items
        if (version_compare($context->getVersion(), '1.6.0') < 0) {
            // Get table name
            $tableName = \Redkiwi\News\Setup\InstallSchema::NEWS_ITEMS_TABLE;
            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                // add gallery column
                $setup->getConnection()->addColumn(
                    $setup->getTable($tableName),
                    'gallery',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => '1M',
                        'nullable' => true,
                        'comment' => 'Gallery'
                    ]
                );
                
            }
        }
        
        // add indexer table
        if (version_compare($context->getVersion(), '1.7.0') < 0) {
            // Get table name
            $tableName = \Redkiwi\News\Setup\InstallSchema::NEWS_BEST_SOLD_PRODUCTS_TABLE;
            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableName) != true) {
                $tableName = $setup->getTable($tableName);
                $table = $setup->getConnection()->newTable($tableName);
                $table->addColumn(
                       'id',
                       Table::TYPE_INTEGER,
                       null,
                       ['identity' => true, 'nullable' => false, 'primary' => true],
                       'ID'
                    )->addColumn(
                        'product_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Product ID'
                    )->addColumn(
                        'store_id',
                        Table::TYPE_SMALLINT, 
                        null,
                        ['unsigned' => true, 'nullable' => false],
                        'Store ID'
                    )->addColumn(
                        'qty_sold',
                        Table::TYPE_INTEGER,
                        null,
                        ['nullable' => false, 'default' => '0'],
                        'Quantity sold'
                    )->addColumn(
                        'updated_at', 
                        Table::TYPE_TIMESTAMP, 
                        null,
                        ['nullable' => true, 'default'  => null],
                        'Update Date'                    
                    )->addIndex(
                        $setup->getIdxName(\Redkiwi\News\Setup\InstallSchema::NEWS_BEST_SOLD_PRODUCTS_TABLE, ['product_id']),
                        ['product_id']
                    )->addIndex(
                        $setup->getIdxName(
                            \Redkiwi\News\Setup\InstallSchema::NEWS_BEST_SOLD_PRODUCTS_TABLE,
                            ['product_id', 'store_id'],
                            \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                        ),
                        ['product_id', 'store_id'],
                        ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
                    )->addForeignKey(
                        $setup->getFkName(
                            \Redkiwi\News\Setup\InstallSchema::NEWS_BEST_SOLD_PRODUCTS_TABLE, 
                            'product_id', 
                            'catalog_product_entity', 
                            'entity_id'
                        ),
                        'product_id',
                        $setup->getTable('catalog_product_entity'),
                        'entity_id',
                        Table::ACTION_CASCADE
                    )->addForeignKey(
                        $setup->getFkName(
                            \Redkiwi\News\Setup\InstallSchema::NEWS_BEST_SOLD_PRODUCTS_TABLE, 
                            'store_id', 
                            'store', 
                            'store_id'
                        ),
                        'store_id',
                        $setup->getTable('store'),
                        'store_id',
                        Table::ACTION_CASCADE
                    )
                    ->setComment('Best Sold Products');
                $setup->getConnection()->createTable($table);
            }
        }

 
        $setup->endSetup();
    }
    
}
