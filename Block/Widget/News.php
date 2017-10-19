<?php
namespace Redkiwi\News\Block\Widget;

use Magento\Framework\Api\SortOrder;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Widget\Block\BlockInterface;
use Redkiwi\News\Model\News as NewsModel;
use Redkiwi\News\Model\CategoryFactory;
use Redkiwi\News\Model\ResourceModel\News\CollectionFactory;
use Redkiwi\News\Helper\Image as ImageHelper;

class News extends Template implements BlockInterface {
    
    /**
     * stores configurations paths
     */
    const CONFIG_PATH_URL_KEY = 'redkiwi_news/general/url_key';
    
    /**
     * @var string
     */
    protected $_template = 'Redkiwi_News::news/widget/latest.phtml';
    
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @var DateTime
     */
    protected $dateTime;
        
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * @var type Redkiwi\News\Helper\Image
     */
    protected $imageHelper;
    
    /**
     * @var type CategoryFactory
     */
    protected $categoryFactory;
    
    /**
     * @var type Category
     */
    protected $category = null;

    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        CategoryFactory $categoryFactory,
        StoreManagerInterface $storeManager,
        DateTime $dateTime,
        ScopeConfigInterface $scopeConfig,
        ImageHelper $imageHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->collectionFactory = $collectionFactory;
        $this->categoryFactory = $categoryFactory;
        $this->storeManager = $storeManager;
        $this->dateTime = $dateTime;
        $this->scopeConfig = $scopeConfig;
        $this->imageHelper = $imageHelper;
    }

    /**
     * Prepare collection with news
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _beforeToHtml() {
        $this->setNewsCollection($this->_getNewsCollection());
        return parent::_beforeToHtml();
    }
    
    /**
     * News collection initialize process
     *
     * @return \Redkiwi\News\Model\ResourceModel\News\Collection
     */
    protected function _getNewsCollection() {
        // get current store ID
        $currentStoreId = $this->storeManager->getStore()->getId();
        // prepare stores array for filter
        $stores = [\Magento\Store\Model\Store::DEFAULT_STORE_ID, $currentStoreId];
        // prepare collection
        $collection = $this->collectionFactory->create()
                ->addFieldToFilter('status', ['eq' => NewsModel::STATUS_ENABLED])
                ->addFieldToFilter('publish_at', ['lteq' => date('Y-m-d H:i:s')])
                ->addFieldToFilter('stores', ['in' => $stores]);
        // add category filter
        if ($this->getCategoryId()) {
            $collection->getSelect()->joinLeft(                    
                \Redkiwi\News\Setup\InstallSchema::NEWS_CATEGORIES_ITEMS_TABLE,
                'news_id=main_table.id',
                ['category_id', 'position']
            ); // add position and categories to collection
            $collection->addFieldToFilter('category_id', ['eq' => (int)$this->getCategoryId()]);
        }
        $collection->setOrder('publish_at', SortOrder::SORT_DESC);
        // add limit
        $collection->setPageSize($this->getLimit())
                ->setCurPage(1);
        return $collection;
    }
    
    /**
     * Prepare title attribute using passed title as parameter 
     * 
     * @return string
     */
    public function getTitle() {        
        return $this->getData('title');
    }
    
    /**
     * Check if image should be displayed or not 
     * 
     * @return boolean
     */
    public function canShowImage() {
        return $this->getData('show_image');
    }
    
    /**
     * Get news list limit
     * 
     * @return int
     */
    protected function getLimit() {
        return (int)$this->getData('limit');
    }
    
    /**
     * Get list category filter or false
     * 
     * @return int OR boolean
     */
    protected function getCategoryId() {
        if ($this->getData('category_id')) {
            return (int)$this->getData('category_id');
        } else {
            return false;
        }
    }
    
    /**
     * Get current category
     *
     * @return \Redkiwi\News\Model\Category
     */
    public function getCurrentCategory() {
        if ($this->category) {
            return $this->category;
        }
        if ($this->getCategoryId()) {
            $category = $this->categoryFactory->create()->load($this->getCategoryId());
            if ($category->getId()) {
                $this->category = $category;
            }
        }
        return $this->category;
    }
    
    /**
     * @param NewsModel $news
     * @return string
     */
    public function getItemUrl(NewsModel $news) {
        $urlPrefix = trim($this->scopeConfig->getValue(self::CONFIG_PATH_URL_KEY, ScopeInterface::SCOPE_STORE));
        $itemUrl = $this->getUrl($urlPrefix . '/' . $news->getUrlKey());
        if ($this->getCurrentCategory()) {
            $itemUrl .= \Redkiwi\News\Controller\Router::CATEGORY_URL_PREFIX . '/' . $this->getCurrentCategory()->getUrlKey() . '/';
        }
        return $itemUrl;
    }
    
    /**
     * Return publish date in locale format
     * 
     * @param NewsModel $news
     * @return string | false
     */
    public function getPublishDate(NewsModel $news) {
        if ($news->getPublishAt()) {
            return $this->dateTime->formatDate($news->getPublishAt(), false);
        }
        return false;
    }
    
    /**
     * Return URL for resized image
     * 
     * @param Redkiwi\News\Model\News $item
     * @param integer $width
     * @param integer $height
     * @return string|false
     */
    public function getImageUrl(NewsModel $item, $width, $height = '') {
        return $this->imageHelper->resize($item->getImage(), $width, $height);
    }    
}
