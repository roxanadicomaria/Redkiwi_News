<?php
namespace Redkiwi\News\Model;

use Redkiwi\News\Api\Data\CategoryInterface;
use Redkiwi\News\Model\ResourceModel\Category as ResourceCategory;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\StoreManagerInterface;

class Category extends AbstractModel implements CategoryInterface, IdentityInterface {
    
    /**#@+
     * News's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * News cache tag
     */
    const CACHE_TAG = 'redkiwi_news_category';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'redkiwi_news_category';
    
     /**
     * Event object
     * 
     * @var string
     */
    protected $_eventObject = 'news_category';
    
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct() {
        $this->_init(ResourceCategory::class);
    }
    
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param StoreManagerInterface $storeManager
     * @param ImageUploader $imageUploader
     * @param FileUploader $fileUploader
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,        
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }
    
     /**
     * Retrieve array of news id's for category
     *
     * The array returned has the following format:
     * array($newsId => $position)
     *
     * @return array
     */
    public function getNewsPosition() {
        if (!$this->getId()) {
            return [];
        }
        $array = $this->getData('news_position');
        if ($array === null) {
            $array = $this->getResource()->getNewsPosition($this);
            $this->setData('news_position', $array);
        }
        return $array;
    }

    
    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities() {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
    
     /**
     * Get id
     *
     * @return int
     */
    public function getId() {
        return $this->getData(self::CATEGORY_ID);
    }
    
    /**
     * Set ID
     *
     * @param int $id
     * @return CategoryInterface
     */
    public function setId($id) {
        return $this->setData(self::CATEGORY_ID, $id);
    }
    
    /**
     * Get Url Key
     *
     * @return string
     */
    public function getUrlKey() {
        return $this->getData(self::URL_KEY);
    }

    /**
     * Set Url Key
     *
     * @param string $url_key
     * @return CategoryInterface
     */
    public function setUrlKey($url_key) {
        return $this->setData(self::URL_KEY, $url_key);
    }
    
    /**
     * Get title
     *
     * @return string
     */
    public function getTitle() {
        return $this->getData(self::TITLE);
    }
    
    /**
     * Set title
     *
     * @param string $title
     * @return CategoryInterface
     */
    public function setTitle($title) {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Get content
     *     
     * @return string
     */
    public function getContent() {
        return $this->getData(self::CONTENT);
    }
    
    /**
     * Set content
     *
     * @param string $content
     * @return CategoryInterface
     */
    public function setContent($content) {
        return $this->setData(self::CONTENT, $content);
    }
    
    /**
     * Get creation date
     *
     * @return string|null
     */
    public function getCreatedAt() {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set creation date
     *
     * @param string $createdAt
     * @return CategoryInterface
     */
    public function setCreatedAt($createdAt) {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get update date
     *
     * @return string|null
     */
    public function getUpdatedAt() {
        return $this->getData(self::UPDATED_AT);
    }
    
    /**
     * Set update date
     *
     * @param string $updateAt
     * @return CategoryInterface
     */
    public function setUpdatedAt($updateAt) {
        return $this->setData(self::UPDATED_AT, $updateAt);
    }
    
    /**
     * Get stores
     *
     * @return string
     */
    public function getStores() {
        return $this->getData(self::STORES);
    }
    
    /**
     * Set stores
     *
     * @param string $stores
     * @return CategoryInterface
     */
    public function setStores($stores) {
        return $this->setData(self::STORES, $stores);
    }
    
    /**
     * Get status
     *
     * @return int
     */
    public function getStatus() {
        return $this->getData(self::STATUS);
    }

    /**
     * Set status
     *
     * @param int $status
     * @return CategoryInterface
     */
    public function setStatus($status) {
        return $this->setData(self::STATUS, $status);
    }
    
    /**
     * Get position
     *
     * @return string
     */
    public function getPosition() {
        return $this->getData(self::POSITION);
    }

    /**
     * Set position
     *
     * @param string $position
     * @return CategoryInterface
     */
    public function setPosition($position) {
        return $this->setData(self::POSITION, $position);
    }
    
    /**
     * Get meta description
     *
     * @return string
     */
    public function getMetaDescription() {
        return $this->getData(self::META_DESCRIPTION);
    }

    /**
     * Set meta description
     *
     * @param string $metaDescription
     * @return CategoryInterface
     */
    public function setMetaDescription($metaDescription) {
        return $this->setData(self::META_DESCRIPTION, $metaDescription);
    }
    
    /**
     * Get meta keywords
     *
     * @return string
     */
    public function getMetaKeywords() {
        return $this->getData(self::META_KEYWORDS);
    }

    /**
     * Set meta keywords
     *
     * @param string $metaKeywords
     * @return CategoryInterface
     */
    public function setMetaKeywords($metaKeywords) {
        return $this->setData(self::META_KEYWORDS, $metaKeywords);
    }
    
}
