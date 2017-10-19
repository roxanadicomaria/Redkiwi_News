<?php
namespace Redkiwi\News\Model\ResourceModel\Category;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Redkiwi\News\Model\Category;
use Redkiwi\News\Model\ResourceModel\Category as CategoryResourceModel;

class Collection extends AbstractCollection {
    
    /**
     * ID Field name
     *
     * @var string
     */
    protected $_idFieldName = \Redkiwi\News\Api\Data\CategoryInterface::CATEGORY_ID;

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'redkiwi_news_category_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'news_category_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct() {
        $this->_init(
            Category::class,
            CategoryResourceModel::class
        );
    }

    /**
     * Get SQL for get record count.
     * Extra GROUP BY strip added.
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql() {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Zend_Db_Select::GROUP);
        return $countSelect;
    }
    
    /**
     * @param string $valueField
     * @param string $labelField
     * @param array $additional
     * @return array
     */
    protected function _toOptionArray($valueField = \Redkiwi\News\Api\Data\CategoryInterface::CATEGORY_ID, $labelField = \Redkiwi\News\Api\Data\CategoryInterface::TITLE, $additional = []) {
        return parent::_toOptionArray($valueField, $labelField, $additional);
    }
    
}
