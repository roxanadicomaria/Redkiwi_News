<?php
namespace Redkiwi\News\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeData implements UpgradeDataInterface {
    
    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        $setup->startSetup();
 
        if (version_compare($context->getVersion(), '1.2.0') < 0) {
            // Get table
            $table = $setup->getTable(\Redkiwi\News\Setup\InstallSchema::NEWS_ITEMS_TABLE);
            
            $setup->getConnection()->update($table, ['image' => 'https://static.pexels.com/photos/29017/pexels-photo-29017.jpg'], ['status = 0']);
            $setup->getConnection()->update($table, ['image' => 'https://static.pexels.com/photos/94287/pexels-photo-94287.jpeg'], ['status = 1']);
        }
        
        // add categories dummy data
        if (version_compare($context->getVersion(), '1.4.0') < 0) {
            // Get table
            $table = $setup->getTable(\Redkiwi\News\Setup\InstallSchema::NEWS_CATEGORIES_TABLE);
            $content = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>';        
            $data = [
                [
                    'url_key' => 'our-team', 
                    'title' => 'Our Team', 
                    'content' => $content,
                    'meta_description' => 'News about our team',
                    'meta_keywords' => 'Team, news',
                    'status' => 1,
                    'position' => 10
                ],
                [
                    'url_key' => 'products', 
                    'title' => 'Products', 
                    'content' => $content,
                    'meta_description' => 'News about our products',
                    'meta_keywords' => 'Products, news',
                    'status' => 1,
                    'position' => 20
                ],
                [
                    'url_key' => 'our-company', 
                    'title' => 'Our Company', 
                    'content' => $content,
                    'meta_description' => 'News about our company',
                    'meta_keywords' => 'Company, news',
                    'status' => 1,
                    'position' => 30
                ],
                [
                    'url_key' => 'industry', 
                    'title' => 'Industry', 
                    'content' => $content,
                    'meta_description' => 'News about industry',
                    'meta_keywords' => 'Industry, news',
                    'status' => 0,
                    'position' => 40
                ]
            ];
        
            // insert multiple rows
            $setup->getConnection()->insertMultiple($table, $data);
        }



        $setup->endSetup();
    }
    
}
