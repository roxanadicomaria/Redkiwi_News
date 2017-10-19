<?php
namespace Redkiwi\News\Model;

use Redkiwi\News\Api\Data\NewsInterface;
use Redkiwi\News\Model\ResourceModel\News as ResourceNews;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\StoreManagerInterface;
use Redkiwi\News\Model\ImageUploader;
use Redkiwi\News\Model\FileUploader;

class News extends AbstractModel implements NewsInterface, IdentityInterface {
    
    /**#@+
     * News's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * News cache tag
     */
    const CACHE_TAG = 'redkiwi_news';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'redkiwi_news_item';
    
     /**
     * Event object
     * 
     * @var string
     */
    protected $_eventObject = 'news_item';
    
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ImageUploader
     */
    protected $imageUploader;
    
     /**
     * @var FileUploader
     */
    protected $fileUploader;
    
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param StoreManagerInterface $storeManager
     * @param ImageUploader $imageUploader
     * * @param FileUploader $fileUploader
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ImageUploader $imageUploader,
        FileUploader $fileUploader,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,        
        array $data = []
    ) {
        $this->storeManager = $storeManager;    
        $this->imageUploader = $imageUploader;
        $this->fileUploader = $fileUploader;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }


    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct() {
        $this->_init(ResourceNews::class);
    }
    
    /**
     * Retrieve category id's for news
     *
     * @return string
     */
    public function getCategories() {
        if (!$this->getId()) {
            return [];
        }
        $data = $this->getData('categories');
        if ($data === null) {
            $array = $this->getResource()->getCategories($this);
            // multiselect will need a string with comma separated values
            $this->setData('categories', implode(',',array_keys($array)));
            if (!$this->getData('categories')) {
                $this->setData('categories', '0'); // no category option
            }
        }
        return $data;
    }
    
    /**
     * Retrieve array of category id's for news
     *
     * The array returned has the following format:
     * array($categoryId => $position)
     *
     * @return array
     */
    public function getCategoriesPosition() {
        if (!$this->getId()) {
            return [];
        }
        $array = $this->getData('categories_position');
        if ($array === null) {
            $array = $this->getResource()->getCategories($this);
            $this->setData('categories_position', $array);
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
        return $this->getData(self::NEWS_ID);
    }
    
    /**
     * Set ID
     *
     * @param int $id
     * @return NewsInterface
     */
    public function setId($id) {
        return $this->setData(self::NEWS_ID, $id);
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
     * @return NewsInterface
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
     * @return NewsInterface
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
     * @return NewsInterface
     */
    public function setContent($content) {
        return $this->setData(self::CONTENT, $content);
    }
    
    /**
     * Get short content
     *     
     * @return string
     */
    public function getShortContent() {
        return $this->getData(self::SHORT_CONTENT);
    }
    
    /**
     * Set short content
     *
     * @param string $shortContent
     * @return NewsInterface
     */
    public function setShortContent($shortContent) {
        return $this->setData(self::SHORT_CONTENT, $shortContent);
    }
    
    /**
     * Get publish date
     *
     * @return string|null
     */
    public function getPublishAt() {
        return $this->getData(self::PUBLISH_AT);
    }

    /**
     * Set publish date
     *
     * @param string $publishAt
     * @return NewsInterface
     */
    public function setPublishAt($publishAt) {
        return $this->setData(self::PUBLISH_AT, $publishAt);
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
     * @return NewsInterface
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
     * @return NewsInterface
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
     * @return NewsInterface
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
     * @return NewsInterface
     */
    public function setStatus($status) {
        return $this->setData(self::STATUS, $status);
    }
    
    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor() {
        return $this->getData(self::AUTHOR);
    }

    /**
     * Set author
     *
     * @param string $author
     * @return NewsInterface
     */
    public function setAuthor($author) {
        return $this->setData(self::AUTHOR, $author);
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
     * @return NewsInterface
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
     * @return NewsInterface
     */
    public function setMetaKeywords($metaKeywords) {
        return $this->setData(self::META_KEYWORDS, $metaKeywords);
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage() {
        return $this->getData(self::IMAGE);
    }
    

    /**
     * Set image
     *
     * @param string $image
     * @return NewsInterface
     */
    public function setImage($image) {
        return $this->setData(self::IMAGE, $image);
    }  
    
    /**
     * Get file
     *
     * @return string
     */
    public function getFile() {
        return $this->getData(self::FILE);
    }

    /**
     * Set file
     *
     * @param string $file
     * @return NewsInterface
     */
    public function setFile($file) {
        return $this->setData(self::FILE, $file);
    }    
    
    /**
     * Get gallery
     *
     * @return string
     */
    public function getGallery() {
        return $this->getData(self::GALLERY);
    }

    /**
     * Set gallery
     *
     * @param string $gallery
     * @return NewsInterface
     */
    public function setGallery($gallery) {
        return $this->setData(self::GALLERY, $gallery);
    }    
    
    /**
     * Retrieve image URL
     *
     * @return string
     */
    public function getImageUrl($imageName = null) {
        $url = false;
        $image = $this->getImage();
        if ($imageName) {
            $image = $imageName;
        }
        $basePath = $this->imageUploader->getBasePath();
        if ($image) {
            if (is_string($image)) {
                $url = $this->storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ) . $basePath . '/' . trim($image, '/');
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Something went wrong while getting the image url.')
                );
            }
        }
        return $url;
    }   
    
    
    /**
     * Retrieve file URL
     *
     * @return string
     */
    public function getFileUrl() {
        $url = false;
        $file = $this->getFile();
        $basePath = $this->fileUploader->getBasePath();
        if ($file) {
            if (is_string($file)) {
                $encodedFileName = base64_encode(trim($file, '/'));
                $url = $this->storeManager->getStore()->getUrl(
                    'news/item/download', 
                    ['file' => $encodedFileName]
                );
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Something went wrong while getting the file url.')
                );
            }
        }
        return $url;
    }


    
}
