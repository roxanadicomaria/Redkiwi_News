<?php

namespace Redkiwi\News\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface {

    /**
     * Table name for News items
     * @var string 
     */
    const NEWS_ITEMS_TABLE = 'redkiwi_news_items';
    
     /**
     * Table name for News categories
     * @var string 
     */
    const NEWS_CATEGORIES_TABLE = 'redkiwi_news_categories'; 
    
     /**
     * Table name for News categories-items association
     * @var string 
     */
    const NEWS_CATEGORIES_ITEMS_TABLE = 'redkiwi_news_categories_items';
    
    /**
     * Table name for best sold products - indexer table
     * @var string 
     */
    const NEWS_BEST_SOLD_PRODUCTS_TABLE = 'redkiwi_best_sold_products';

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists(self::NEWS_ITEMS_TABLE)) {
            $tableName = $installer->getTable(self::NEWS_ITEMS_TABLE);
            $table = $installer->getConnection()->newTable($tableName);
            $table->addColumn(
                        'id', Table::TYPE_INTEGER, null, [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                            ], 'News Item ID'
                    )
                    ->addColumn(
                            'url_key', Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => ''], 'URL Key'
                    )
                    ->addColumn(
                            'title', Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => ''], 'Title'
                    )
                    ->addColumn(
                            'content', Table::TYPE_TEXT, '1M', ['nullable' => true], 'Content'
                    )
                    ->addColumn(
                            'short_content', Table::TYPE_TEXT, '64k', ['nullable' => true], 'Short Content'
                    )
                    ->addColumn(
                            'publish_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT], 'Publish At'
                    )
                    ->addColumn(
                            'created_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT], 'Created At'
                    )
                    ->addColumn(
                            'updated_at', Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE], 'Updated At'
                    )
                    ->addColumn(
                            'stores', Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => '0'], 'Display on Stores'
                    )
                    ->addColumn(
                            'status', Table::TYPE_BOOLEAN, null, ['unsigned' => true, 'nullable' => false, 'default' => '1'], 'Status'
                    )
                    ->addColumn(
                            'author', Table::TYPE_TEXT, 255, ['nullable' => false, 'default' => ''], 'Author'
                    )
                    ->addColumn(
                            'meta_description', Table::TYPE_TEXT, '64k', ['nullable' => false, 'default' => ''], 'Meta Description'
                    )
                    ->addColumn(
                            'meta_keywords', Table::TYPE_TEXT, 512, ['nullable' => false, 'default' => ''], 'Meta Keywords'
                    )
                    ->addIndex(
                            $installer->getIdxName($tableName, ['url_key'], AdapterInterface::INDEX_TYPE_INDEX), ['url_key'], ['type' => AdapterInterface::INDEX_TYPE_INDEX]
                    )
                    ->addIndex(
                            $installer->getIdxName($tableName, ['title', 'content', 'short_content'], AdapterInterface::INDEX_TYPE_FULLTEXT), ['title', 'content', 'short_content'], ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
                    )
                    ->setComment('News Items');
            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }

}
