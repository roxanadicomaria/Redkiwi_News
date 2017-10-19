<?php
namespace Redkiwi\News\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Event\ManagerInterface;
use Redkiwi\News\Api\Data\CategoryInterface;

class Category extends AbstractDb {
    
    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var EntityManager
     */
    protected $entityManager;
    
     /**
     * Category news table name
     *
     * @var string
     */
    protected $categoryNewsTable;
    
    /**
     * Core event manager proxy
     *
     * @var ManagerInterface
     */
    protected $_eventManager = null;

    
    /**
     * @param Context $context
     * @param DateTime $dateTime
     * @param EntityManager $entityManager
     * @param ManagerInterface $eventManager
     * @param type $connectionName
     */
    public function __construct(
        Context $context,
        DateTime $dateTime,
        EntityManager $entityManager,
        ManagerInterface $eventManager,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->dateTime = $dateTime;
        $this->entityManager = $entityManager;
        $this->_eventManager = $eventManager;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct() {
        $this->_init(\Redkiwi\News\Setup\InstallSchema::NEWS_CATEGORIES_TABLE, CategoryInterface::CATEGORY_ID);
    }

    /**
     * Process data before saving
     *
     * @param AbstractModel $object
     * @return $this
     * @throws LocalizedException
     */
    protected function _beforeSave(AbstractModel $object) {
        /*
         * For 3 attributes which represent timestamp data in DB
         * we should make converting such as:
         * If they are empty we need to convert them into DB
         * type NULL so in DB they will be empty and not some default value
         */
        foreach (['created_at', 'updated_at'] as $field) {
            $value = !$object->getData($field) ? null : $this->dateTime->formatDate($object->getData($field));
            $object->setData($field, $value);
        }

        if (!$this->isValidCategoryUrl($object)) {
            throw new LocalizedException(
                __('The category URL key contains capital letters or disallowed symbols.')
            );
        }

        if ($this->isNumericCategoryUrl($object)) {
            throw new LocalizedException(
                __('The category URL key cannot be made of only numbers.')
            );
        }
        return parent::_beforeSave($object);
    }
    
    /**
     *  Check whether url_key is numeric
     *
     * @param AbstractModel $object
     * @return bool
     */
    protected function isNumericCategoryUrl(AbstractModel $object) {
        return preg_match('/^[0-9]+$/', $object->getData(CategoryInterface::URL_KEY));
    }

    /**
     *  Check whether url_key is valid
     *
     * @param AbstractModel $object
     * @return bool
     */
    protected function isValidCategoryUrl(AbstractModel $object) {
        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData(CategoryInterface::URL_KEY));
    }
    
    /**
     * Get positions of associated to category news
     *
     * @param \Magento\Catalog\Model\Category $category
     * @return array
     */
    public function getNewsPosition($category) {
        $select = $this->getConnection()->select()->from(
            $this->getCategoryNewsTable(),
            ['news_id', 'position']
        )->where(
            'category_id = :category_id'
        );
        $bind = ['category_id' => (int)$category->getId()];
        return $this->getConnection()->fetchPairs($select, $bind);
    }
    
    /**
     * Category news table name getter
     *
     * @return string
     */
    public function getCategoryNewsTable() {
        if (!$this->categoryNewsTable) {
            $this->categoryNewsTable = $this->getTable(\Redkiwi\News\Setup\InstallSchema::NEWS_CATEGORIES_ITEMS_TABLE);
        }
        return $this->categoryNewsTable;
    }
    
    /**
     * Process category data after save category object
     * save related news ids
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object) {
        $this->_saveCategoryNews($object);
        return parent::_afterSave($object);
    }
    
    /**
     * Save category news relation
     *
     * @param \Redkiwi\News\Model\Category $category
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _saveCategoryNews($category) {
        $id = $category->getId();
        /**
         * new category-news relationships
         */
        $news = $category->getPostedNews();
        /**
         * Example re-save category
         */
        if ($news === null) {
            return $this;
        }
        /**
         * old category-news relationships
         */
        $oldNews = $category->getNewsPosition();
        $insert = array_diff_key($news, $oldNews);
        $delete = array_diff_key($oldNews, $news);
        /**
         * Find news ids which are presented in both arrays
         * and saved before (check $oldNews array)
         */
        $update = array_intersect_key($news, $oldNews);
        $update = array_diff_assoc($update, $oldNews);
        $connection = $this->getConnection();
        /**
         * Delete news from category
         */
        if (!empty($delete)) {
            $cond = ['news_id IN(?)' => array_keys($delete), 'category_id=?' => $id];
            $connection->delete($this->getCategoryNewsTable(), $cond);
        }
        /**
         * Add news to category
         */
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $newsId => $position) {
                $data[] = [
                    'category_id' => (int)$id,
                    'news_id' => (int)$newsId,
                    'position' => (int)$position,
                ];
            }
            $connection->insertMultiple($this->getCategoryNewsTable(), $data);
        }
        /**
         * Update news positions in category
         */
        if (!empty($update)) {
            foreach ($update as $newsId => $position) {
                $where = ['category_id = ?' => (int)$id, 'news_id = ?' => (int)$newsId];
                $bind = ['position' => (int)$position];
                $connection->update($this->getCategoryNewsTable(), $bind, $where);
            }
        }
        if (!empty($insert) || !empty($delete)) {
            $newsIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $this->_eventManager->dispatch(
                'redkiwi_news_category_change_news',
                ['category' => $category, 'news_ids' => $newsIds]
            );
        }
        return $this;
    }

    
}
