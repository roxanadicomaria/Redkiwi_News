<?php
namespace Redkiwi\News\Controller\Repository\Category;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Redkiwi\News\Api\CategoryRepositoryInterface;

class Listing extends Action {
    
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
     * 
     * @param Context $context
     * @param CategoryRepositoryInterface $categoryRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param SortOrder $sortOrder
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        CategoryRepositoryInterface $categoryRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        SortOrder $sortOrder,
        StoreManagerInterface $storeManager 
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->sortOrder = $sortOrder;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Show a list with active categories
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
        
        foreach ($categories as $item) {            
            $this->getResponse()->appendBody(sprintf(
                    "%s (%d)\n",
                    $item->getTitle(),
                    $item->getId()
            ));
        }
    }
    
}
