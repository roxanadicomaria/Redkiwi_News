<?php
namespace Redkiwi\News\Block;
 
use Magento\Framework\Registry;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Theme\Block\Html\Pager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Cms\Model\Template\FilterProvider;
use Redkiwi\News\Model\News;
use Redkiwi\News\Model\ResourceModel\News\CollectionFactory as NewsCollectionFactory;
use Redkiwi\News\Helper\Image as ImageHelper;
 
class ItemsList extends Template {
    
    /**
     * stores configurations paths
     */
    const CONFIG_PATH_URL_KEY = 'redkiwi_news/general/url_key';
    const CONFIG_PATH_DESCRIPTION = 'redkiwi_news/general/description';
    const CONFIG_PATH_ALLOWED_ITEMS = 'redkiwi_news/general/allowed_items';
    
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @var NewsCollectionFactory
     */
    protected $newsCollectionFactory;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * @var \Redkiwi\News\Model\ResourceModel\News\Collection
     */
    protected $news;
    
    /**
     * @var DateTime
     */
    protected $dateTime;
    
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $filterProvider;
    
    // Redkiwi\News\Helper\Image
    protected $imageHelper;
    
    /**
     * @var Registry
     */
    protected $coreRegistry;
    
    /**
     * 
     * @param Context $context
     * @param NewsCollectionFactory $newsCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param DateTime $dateTime
     * @param ScopeConfigInterface $scopeConfig
     * @param FilterProvider $filterProvider
     * @param ImageHelper $imageHelper
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        NewsCollectionFactory $newsCollectionFactory,
        StoreManagerInterface $storeManager,
        DateTime $dateTime,
        ScopeConfigInterface $scopeConfig,
        FilterProvider $filterProvider,
        ImageHelper $imageHelper,
        Registry $registry,
        array $data = []
    ) {
        $this->newsCollectionFactory = $newsCollectionFactory;
        $this->storeManager = $storeManager;
        $this->dateTime = $dateTime;
        $this->scopeConfig = $scopeConfig;
        $this->filterProvider = $filterProvider;
        $this->imageHelper = $imageHelper;
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }
    
    /**
     * @return \Redkiwi\News\Model\ResourceModel\News\Collection
     */
    public function getNewsItems() {
        if (is_null($this->news)) {
            // get current store ID
            $currentStoreId = $this->storeManager->getStore()->getId();
            // prepare stores array for filter
            $stores = [\Magento\Store\Model\Store::DEFAULT_STORE_ID, $currentStoreId];
            // prepare collection
            $this->news = $this->newsCollectionFactory->create()
                    ->addFieldToFilter('status', ['eq' => 1])
                    ->addFieldToFilter('publish_at', ['lteq' => date('Y-m-d H:i:s')])
                    ->addFieldToFilter('stores', ['in' => $stores]);
            // add category filter
            if ($this->getCurrentCategory()) {
                $this->news->getSelect()->joinLeft(                    
                    \Redkiwi\News\Setup\InstallSchema::NEWS_CATEGORIES_ITEMS_TABLE,
                    'news_id=main_table.id',
                    ['category_id', 'position']
                ); // add position and categories to collection
                $this->news->addFieldToFilter('category_id', ['eq' => (int)$this->getCurrentCategory()->getId()]);
            }

            $this->news->setOrder('publish_at', SortOrder::SORT_DESC);            
        }
        return $this->news;
    }    
    
    /**
     * get current category
     *
     * @return \Redkiwi\News\Model\Category
     */
    public function getCurrentCategory() {
        return $this->coreRegistry->registry('current_category');
    }

    /**
     * @return $this
     */
    protected function _prepareLayout() {
        parent::_prepareLayout();
                
        /** @var \Magento\Theme\Block\Html\Pager $pager */
        $pager = $this->getLayout()->createBlock(
                Pager::class, 
                'redkiwi.news.list.pager'
            );
        $pager->setShowAmounts(true)
            ->setAvailableLimit($this->getAvailablePagerLimit())
            ->setCollection($this->getNewsItems());
        $this->setChild('pager', $pager);
        $this->getNewsItems()->load();
        return $this;
    }
        
     /**
     * Get pager available limits
     * 
     * @return array
     */
    protected function getAvailablePagerLimit() {
        $allowedPages = $this->scopeConfig->getValue(self::CONFIG_PATH_ALLOWED_ITEMS, ScopeInterface::SCOPE_STORE);
        $availableLimit = [5 => 5, 10 => 10, 15 => 15]; // default values
        if ($allowedPages) {
            $availableLimit = [];
            $allowedPagesArray = explode(',',$allowedPages);
            foreach ($allowedPagesArray as $item) {
                $availableLimit[(int)$item] = (int)$item;
            }
        }
        return $availableLimit;
    }   
    
    /**
     * @return string
     */
    public function getPagerHtml() {
        return $this->getChildHtml('pager');
    }
    
    /**
     * @param News $news
     * @return string
     */
    public function getItemUrl(News $news) {
        $urlPrefix = trim($this->scopeConfig->getValue(self::CONFIG_PATH_URL_KEY, ScopeInterface::SCOPE_STORE));
        $itemUrl = $this->getUrl($urlPrefix . '/' . $news->getUrlKey());
        if ($this->getCurrentCategory()) {
            $itemUrl .= \Redkiwi\News\Controller\Router::CATEGORY_URL_PREFIX . '/' . $this->getCurrentCategory()->getUrlKey() . '/';
        }
        return $itemUrl;
    }

    
    /**
     * Get list description from configurations
     * 
     * @return string
     */
    public function getDescription() {
        return trim($this->scopeConfig->getValue(self::CONFIG_PATH_DESCRIPTION, ScopeInterface::SCOPE_STORE));
    }
    
    /**
     * Return publish date in locale format
     * 
     * @param News $news
     * @return string | false
     */
    public function getPublishDate(News $news) {
        if ($news->getPublishAt()) {
            return $this->dateTime->formatDate($news->getPublishAt(), false);
        }
        return false;
    }
    
    /**
     * Prepare HTML content
     * 
     * @param News $news
     * @return string
     */
    public function getShortContent(News $news) {
        $content = $this->filterProvider->getPageFilter()->filter($news->getShortContent());
        return $content;
    }

    /**
     * Return URL for resized image
     * 
     * @param Redkiwi\News\Model\News $item
     * @param integer $width
     * @param integer $height
     * @return string|false
     */
    public function getImageUrl(News $item, $width, $height = '') {
        return $this->imageHelper->resize($item->getImage(), $width, $height);
    }

    
}
