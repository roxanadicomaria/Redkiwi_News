<?php
namespace Redkiwi\News\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Redkiwi\News\Api\CategoryRepositoryInterface;
use Redkiwi\News\Model\Category;

class CategoryMenu extends Template {
    
    /**
     * stores configurations paths
     */
    const CONFIG_PATH_URL_KEY = 'redkiwi_news/general/url_key';
    
    /**
     * @var CategoryRepositoryInterface 
     */
    private $categoryRepository;
    
    /**
     * @var SearchCriteriaBuilder 
     */
    private $searchCriteriaBuilder;
    
    /**
     * @var FilterBuilder 
     */
    private $filterBuilder;
    
    /**
     * @var SortOrder 
     */
    private $sortOrder;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @param Context $context
     * @param CategoryRepositoryInterface $categoryRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param SortOrder $sortOrder
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        CategoryRepositoryInterface $categoryRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        SortOrder $sortOrder,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->sortOrder = $sortOrder;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    /**
     * Get all active categories
     * 
     * @return Redkiwi\News\Model\Category
     */
    public function getCategories() {
        // get current store ID
        $currentStoreId = $this->storeManager->getStore()->getId();
        // prepare stores array for filter
        $stores = [\Magento\Store\Model\Store::DEFAULT_STORE_ID, $currentStoreId];
        // prepare filters
        $filters = [
            // active status filter
            $this->filterBuilder->setConditionType('eq')->setField('status')->setValue(1)->create(),
            // category stores contains admin (0) or current store ID
            $this->filterBuilder->setConditionType('in')->setField('stores')->setValue($stores)->create()
        ];
        // apply filters
        $this->searchCriteriaBuilder->addFilters($filters);
        // prepare sort order
        $orders = [
            // order by position: low number first
            $this->sortOrder->setField('position')->setDirection(SortOrder::SORT_ASC)
        ];
        // apply sort order
        $this->searchCriteriaBuilder->setSortOrders($orders);
        $categories = $this->categoryRepository->getList(
            $this->searchCriteriaBuilder->create()
        )->getItems();
        return $categories;
    }
    
    /**
     * Get category URL 
     * 
     * @param Category $category
     * @return string
     */
    public function getCategoryLink(Category $category) {
        $urlPrefix = trim($this->scopeConfig->getValue(self::CONFIG_PATH_URL_KEY, ScopeInterface::SCOPE_STORE),'/');
        return $this->getUrl($urlPrefix . '/' . \Redkiwi\News\Controller\Router::CATEGORY_URL_PREFIX . '/' . $category->getUrlKey());
    }
    
}
