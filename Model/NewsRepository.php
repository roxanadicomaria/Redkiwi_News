<?php
namespace Redkiwi\News\Model;
 
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Redkiwi\News\Api\Data\NewsInterface;
use Redkiwi\News\Api\Data\NewsInterfaceFactory;
use Redkiwi\News\Api\Data\NewsSearchResultInterface;
use Redkiwi\News\Api\Data\NewsSearchResultInterfaceFactory;
use Redkiwi\News\Api\NewsRepositoryInterface;
use Redkiwi\News\Model\ResourceModel\News as ResourceNews;
use Redkiwi\News\Model\ResourceModel\News\CollectionFactory as NewsCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
 
class NewsRepository implements NewsRepositoryInterface {
    
    /**
     * Cached instances
     * 
     * @var array
     */
    protected $instances = [];
    
    /**
     * @var ResourceNews
     */
    protected $resource;
 
    /**
     * @var NewsInterface
     */
    protected $newsInterfaceFactory;
 
    /**
     * @var NewsCollectionFactory
     */
    protected $newsCollectionFactory;
 
    /**
     * @var Data\NewsSearchResultInterfaceFactory
     */
    protected $searchResultsFactory;
 
    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;
    
    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;
 
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * 
     * @param ResourceNews $resource
     * @param NewsCollectionFactory $newsCollectionFactory
     * @param NewsInterfaceFactory $newsInterfaceFactory
     * @param NewsSearchResultInterfaceFactory $newsSearchResultsInterfaceFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceNews $resource,
        NewsCollectionFactory $newsCollectionFactory,
        NewsInterfaceFactory $newsInterfaceFactory,
        NewsSearchResultInterfaceFactory $newsSearchResultsInterfaceFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource                 = $resource;
        $this->newsCollectionFactory    = $newsCollectionFactory;
        $this->newsInterfaceFactory     = $newsInterfaceFactory;
        $this->searchResultsFactory     = $newsSearchResultsInterfaceFactory;
        $this->dataObjectHelper         = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager             = $storeManager;
    }
 
    /**
     * Save data
     *
     * @param NewsInterface $news
     * @return NewsInterface
     * @throws CouldNotSaveException
     */
    public function save(NewsInterface $news) {
        if (!$news->getStores()) {
            $storeId = 0; // all stores
            $news->setStores($storeId);
        }
        try {
            $this->resource->save($news);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the news: %1',
                $exception->getMessage()
            ));
        }
        return $news;
    }
    
    /**
     * Load news data by given id
     *
     * @param int $id
     * @return NewsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id) {
        if (!isset($this->instances[$id])) {
            $news = $this->newsInterfaceFactory->create();
            $news->load($id);
            if (!$news->getId()) {
                throw new NoSuchEntityException(__('News item with id "%1" does not exist.', $id));
            }
            $this->instances[$id] = $news;
        }
        return $this->instances[$id];
    }
    
    /**
     * Load data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return NewsSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $criteria) {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
 
        $collection = $this->newsCollectionFactory->create();        
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {                
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $news = [];
        /** @var NewsInterface $newsModel */
        foreach ($collection as $newsModel) {
            $newsDataObject = $this->newsInterfaceFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $newsDataObject,
                $newsModel->getData(),
                NewsInterface::class
            );
            $news[] = $newsDataObject;
        }
        $searchResults->setItems($news);
        return $searchResults;
    }
    
    /**
     * Delete news
     *
     * @param NewsInterface $news
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(NewsInterface $news) {
        $id = $news->getId();
        try {
            if (isset($this->instances[$id])) {
                unset($this->instances[$id]);
            }
            $this->resource->delete($news);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the news: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }
 
    /**
     * Delete news by given Identity
     *
     * @param int $id
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($id) {
        return $this->delete($this->getById($id));
    }
    
}
