<?php
namespace Redkiwi\News\Model\ResourceModel;
 
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Event\ManagerInterface;
use Redkiwi\News\Api\Data\NewsInterface;
use Redkiwi\News\Model\ImageUploader;
use Redkiwi\News\Model\FileUploader;
 
class News extends AbstractDb {
    
    /**
     * @var DateTime
     */
    protected $dateTime;
 
    /**
     * @var EntityManager
     */
    protected $entityManager;
    
     /**
     * @var ImageUploader
     */
    protected $imageUploader;
    
    /**
     * @var FileUploader
     */
    protected $fileUploader;

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
     * @param ImageUploader $imageUploader
     * @param FileUploader $fileUploader
     * @param ManagerInterface $eventManager
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        DateTime $dateTime,
        EntityManager $entityManager,
        ImageUploader $imageUploader,
        FileUploader $fileUploader,
        ManagerInterface $eventManager,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->dateTime = $dateTime;
        $this->entityManager = $entityManager;
        $this->imageUploader = $imageUploader;
        $this->fileUploader = $fileUploader;
        $this->_eventManager = $eventManager;
    }
 
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct() {
        $this->_init(\Redkiwi\News\Setup\InstallSchema::NEWS_ITEMS_TABLE, NewsInterface::NEWS_ID);
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
        foreach (['publish_at', 'created_at', 'updated_at'] as $field) {
            $value = !$object->getData($field) ? null : $this->dateTime->formatDate($object->getData($field));
            $object->setData($field, $value);
        }
 
        if (!$this->isValidNewsUrl($object)) {
            throw new LocalizedException(
                __('The news URL key contains capital letters or disallowed symbols.')
            );
        }
 
        if ($this->isNumericNewsUrl($object)) {
            throw new LocalizedException(
                __('The news URL key cannot be made of only numbers.')
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
    protected function isNumericNewsUrl(AbstractModel $object) {
        return preg_match('/^[0-9]+$/', $object->getData(NewsInterface::URL_KEY));
    }
 
    /**
     *  Check whether url_key is valid
     *
     * @param AbstractModel $object
     * @return bool
     */
    protected function isValidNewsUrl(AbstractModel $object) {
        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData(NewsInterface::URL_KEY));
    }
    
    /**
     * Delete attached object images from server
     * 
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterDelete(AbstractModel $object) {
        // delete image
        if ($image = $object->getData(NewsInterface::IMAGE)) {
            $this->imageUploader->deleteImage($image);
        }
        // delete file
        if ($file = $object->getData(NewsInterface::FILE)) {
            $this->fileUploader->deleteFile($file);
        }
        // delete gallery images
        if ($values = $object->getData(NewsInterface::GALLERY)) {        
            // unserialize values
            $values = unserialize($values);
            foreach ($values as $key => $value) {
                // delete image
                if (isset($value['image'])) {
                    $imageName = $value['image'];
                    $this->imageUploader->deleteImage($imageName);
                }
            }
        }
        return parent::_afterDelete($object);
    }

     /**
     * Get associated categories to news
     *
     * @param \Magento\Catalog\Model\News $news
     * @return array
     */
    public function getCategories($news) {
        $select = $this->getConnection()->select()->from(
            $this->getCategoryNewsTable(),
            ['category_id', 'position']
        )->where(
            'news_id = :news_id'
        );
        $bind = ['news_id' => (int)$news->getId()];
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
     * Process category data after save news object
     * save related category ids
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
     * @param \Redkiwi\News\Model\News $news
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _saveCategoryNews($news) {
        $id = $news->getId();
        /**
         * new category-news relationships
         */
        $categories = $news->getPostedCategories();
        /**
         * Example re-save news
         */
        if ($categories === null) {
            return $this;
        }
        /**
         * old category-news relationships
         */
        $oldCategories = array_keys($news->getCategoriesPosition());
        $insert = array_diff($categories, $oldCategories);
        $delete = array_diff($oldCategories, $categories);        
        $connection = $this->getConnection();
        /**
         * Delete categories from news
         */
        if (!empty($delete)) {
            $cond = ['category_id IN(?)' => $delete, 'news_id=?' => $id];
            $connection->delete($this->getCategoryNewsTable(), $cond);
        }
        /**
         * Add categories to news
         */
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $categoryId) {
                if ($categoryId) {
                    $data[] = [
                        'category_id' => (int)$categoryId,
                        'news_id' => (int)$id,
                        'position' => 0, // no position provided from news form
                    ];
                }
            }
            if ($data) {
                $connection->insertMultiple($this->getCategoryNewsTable(), $data);
            }
        }
        if (!empty($insert) || !empty($delete)) {
            $categoryIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $this->_eventManager->dispatch(
                'redkiwi_news_category_change_categories',
                ['news' => $news, 'category_ids' => $categoryIds]
            );
        }
        return $this;
    }

}
