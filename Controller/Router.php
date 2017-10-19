<?php
namespace Redkiwi\News\Controller;
 
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Redkiwi\News\Api\CategoryRepositoryInterface;
use Redkiwi\News\Model\ResourceModel\News\CollectionFactory as NewsCollectionFactory;
 
class Router implements RouterInterface {
    
    /**
     * stores configurations paths
     */
    const CONFIG_PATH_URL_KEY = 'redkiwi_news/general/url_key';
    const CATEGORY_URL_PREFIX = 'category';
    
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;
 
    /**
     * Event manager
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;
 
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
 
    /**
     * @var NewsCollectionFactory
     */
    protected $newsCollectionFactory;
 
    /**
     * Response
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $response;
    
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
     * 
     * @param ActionFactory $actionFactory
     * @param EventManagerInterface $eventManager
     * @param NewsCollectionFactory $newsCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param ResponseInterface $response
     * @param ScopeConfigInterface $scopeConfig
     * @param CategoryRepositoryInterface $categoryRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param SortOrder $sortOrder

     */
    public function __construct(
        ActionFactory $actionFactory,
        EventManagerInterface $eventManager,
        NewsCollectionFactory $newsCollectionFactory,
        StoreManagerInterface $storeManager,
        ResponseInterface $response,
        ScopeConfigInterface $scopeConfig,
        CategoryRepositoryInterface $categoryRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        SortOrder $sortOrder
    ) {
        $this->actionFactory = $actionFactory;
        $this->eventManager = $eventManager;
        $this->newsCollectionFactory = $newsCollectionFactory;
        $this->storeManager = $storeManager;
        $this->response = $response;
        $this->scopeConfig = $scopeConfig;
        $this->categoryRepository = $categoryRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->sortOrder = $sortOrder;

    }
 
    /**
     * Validate and Match News Page and modify request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request) {
        $identifier = trim($request->getPathInfo(), '/');
        $condition = new \Magento\Framework\DataObject(['identifier' => $identifier, 'continue' => true]);
        $this->eventManager->dispatch(
            'redkiwi_news_controller_router_match_before',
            ['router' => $this, 'condition' => $condition]
        );
        $identifier = $condition->getIdentifier();
        if ($condition->getRedirectUrl()) {
            $this->response->setRedirect($condition->getRedirectUrl());
            $request->setDispatched(true);
            return $this->actionFactory->create('Magento\Framework\App\Action\Redirect');
        }
        if (!$condition->getContinue()) {
            return null;
        }        
        $identifierParts = explode('/', $identifier);
        $urlPrefix = trim($this->scopeConfig->getValue(self::CONFIG_PATH_URL_KEY, ScopeInterface::SCOPE_STORE),'/');
        // check if $urlPrefix is the first identifier part
        if(!is_array($identifierParts) || $identifierParts[0] != $urlPrefix) {
            // not news page request, continue
            return null;
        }
        if (count($identifierParts) > 2 && $identifierParts[1] == self::CATEGORY_URL_PREFIX) { // check if category page
            // get category item URL key as the 2nd indetifier part
            $categoryUrlKey = $identifierParts[2];            
             /** get category item based on identifier */
            $categories = $this->getCategoriesByUrlKey($categoryUrlKey);
            foreach ($categories as $item) { 
                if ($item && $item->getId()) {
                    // prepare request
                    $request->setModuleName('news')
                            ->setControllerName('category')
                            ->setActionName('view')
                            ->setParam('id', $item->getId());
                    $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
                    break;
                }
            }
        } elseif (count($identifierParts) > 1) {
            // get news item URL key as the 2nd indetifier part
            $newsUrlKey = $identifierParts[1];        
            /** get news item based on identifier */
            // get current store ID
            $currentStoreId = $this->storeManager->getStore()->getId();
            // prepare stores array for filter
            $stores = [\Magento\Store\Model\Store::DEFAULT_STORE_ID, $currentStoreId];
            // get first news item that fulfils conditions
            $news = $this->newsCollectionFactory->create()
                    ->addFieldToFilter('status', ['eq' => 1])
                    ->addFieldToFilter('publish_at', ['lteq' => date('Y-m-d H:i:s')])
                    ->addFieldToFilter('stores', ['in' => $stores])
                    ->addFieldToFilter('url_key', $newsUrlKey)
                    ->getFirstItem();        
            if (!$news || !$news->getId()) {
                return null;
            }   
            /**
            * try to load category if there is a parameter
            */
            $categoryId = null;
            if (count($identifierParts) > 3 && $identifierParts[2] == self::CATEGORY_URL_PREFIX) { 
                // get category item URL key as the 2nd indetifier part
                $categoryUrlKey = $identifierParts[3];
                $categories = $this->getCategoriesByUrlKey($categoryUrlKey);
                foreach ($categories as $item) { 
                    if ($item && $item->getId()) {
                        $categoryId = $item->getId();
                        break;
                    }
                }
            }  
            // prepare request
            $request->setModuleName('news')
                    ->setControllerName('item')
                    ->setActionName('view')
                    ->setParam('id', $news->getId()); 
            if ($categoryId) {
                $request->setParam('catid', $categoryId);
            }
            $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
        } else {
            // redirect to news list
            // prepare request
            $request->setModuleName('news')
                    ->setControllerName('index')
                    ->setActionName('index');
            $request->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);
        }

        return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
    }  
    
    /**
     * Get categories by url_key
     * 
     * @param string $categoryUrlKey
     * @return \Redkiwi\News\Model\ResourceModel\Category\Collection
     */
    protected function getCategoriesByUrlKey($categoryUrlKey) {
        /** get category item based on identifier */
        // get current store ID
        $currentStoreId = $this->storeManager->getStore()->getId();
        // prepare stores array for filter
        $stores = [\Magento\Store\Model\Store::DEFAULT_STORE_ID, $currentStoreId];
        // get first category item that fulfils conditions
        // prepare filters
        $filters = [
            // active status filter
            $this->filterBuilder->setConditionType('eq')->setField(\Redkiwi\News\Api\Data\CategoryInterface::STATUS)->setValue(\Redkiwi\News\Model\Category::STATUS_ENABLED)->create(),
            // URL key check
            $this->filterBuilder->setConditionType('eq')->setField(\Redkiwi\News\Api\Data\CategoryInterface::URL_KEY)->setValue($categoryUrlKey)->create(),
            // category stores contains admin (0) or current store ID
            $this->filterBuilder->setConditionType('in')->setField(\Redkiwi\News\Api\Data\CategoryInterface::STORES)->setValue($stores)->create()
        ];
        // apply filters
        $this->searchCriteriaBuilder->addFilters($filters);
        // prepare sort order
        $orders = [
            // order by position: low number first
            $this->sortOrder->setField(\Redkiwi\News\Api\Data\CategoryInterface::POSITION)->setDirection(SortOrder::SORT_ASC)
        ];
        // apply sort order
        $this->searchCriteriaBuilder->setSortOrders($orders);
        $categories = $this->categoryRepository->getList(
            $this->searchCriteriaBuilder->create()
        )->getItems();
        return $categories;
    }
    

}
