<?php
namespace Redkiwi\News\Controller\Repository;
 
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Redkiwi\News\Api\NewsRepositoryInterface;
 
class ListAction extends Action {
    
    /**
     * @var NewsRepositoryInterface 
     */
    private $newsRepository;
    
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
     * 
     * @param Context $context
     * @param NewsRepositoryInterface $newsRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param SortOrder $sortOrder
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        NewsRepositoryInterface $newsRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        SortOrder $sortOrder,
        StoreManagerInterface $storeManager 
    ) {
        $this->newsRepository = $newsRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->sortOrder = $sortOrder;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }
 
    /**
     * Show a list with active news
     */
    public function execute() {
        $this->getResponse()->setHeader('content-type', 'text/plain');
        // get current store ID
        $currentStoreId = $this->storeManager->getStore()->getId();
        // prepare stores array for filter
        $stores = [\Magento\Store\Model\Store::DEFAULT_STORE_ID, $currentStoreId];
        // prepare filters
        $filters = [
            // active status filter
            $this->filterBuilder->setConditionType('eq')->setField('status')->setValue(1)->create(),
            // publish date filter - publish date is older or equal to today
            $this->filterBuilder->setConditionType('lteq')->setField('publish_at')->setValue(date('Y-m-d H:i:s'))->create(),
            // news stores contains admin (0) or current store ID
            $this->filterBuilder->setConditionType('in')->setField('stores')->setValue($stores)->create()
        ];
        // apply filters
        $this->searchCriteriaBuilder->addFilters($filters);
        // prepare sort order
        $orders = [
            // order by publish date: newest items first
            $this->sortOrder->setField('publish_at')->setDirection(SortOrder::SORT_DESC)
        ];
        // apply sort order
        $this->searchCriteriaBuilder->setSortOrders($orders);
        $news = $this->newsRepository->getList(
            $this->searchCriteriaBuilder->create()
        )->getItems();
        
        foreach ($news as $item) {            
            $this->getResponse()->appendBody(sprintf(
                    "%s (%d)\n",
                    $item->getTitle(),
                    $item->getId()
            ));
        }
    }
    
}
