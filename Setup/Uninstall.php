<?php
namespace Redkiwi\News\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface {
    
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.Generic.CodeAnalysis.UnusedFunctionParameter)
     */
    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        // uninstall news items table
        $tableName = \Redkiwi\News\Setup\InstallSchema::NEWS_ITEMS_TABLE;
        if ($setup->tableExists($tableName)) {
            $setup->getConnection()->dropTable($tableName);
        }
        
        // unistall categories table
        $tableName = \Redkiwi\News\Setup\InstallSchema::NEWS_CATEGORIES_TABLE;
        if ($setup->tableExists($tableName)) {
            $setup->getConnection()->dropTable($tableName);
        }
        
        // uninstall best sold products table
        $tableName = \Redkiwi\News\Setup\InstallSchema::NEWS_BEST_SOLD_PRODUCTS_TABLE;
        if ($setup->tableExists($tableName)) {
            $setup->getConnection()->dropTable($tableName);
        }


    }
    
}
