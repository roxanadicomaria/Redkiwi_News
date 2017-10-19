<?php
namespace Redkiwi\News\Model;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Redkiwi\News\Api\Data\CategoryInterface;
use Redkiwi\News\Api\Data\CategoryInterfaceFactory;
use Redkiwi\News\Api\Data\CategorySearchResultInterface;
use Redkiwi\News\Api\Data\CategorySearchResultInterfaceFactory;
use Redkiwi\News\Api\CategoryRepositoryInterface;
use Redkiwi\News\Model\ResourceModel\Category as ResourceCategory;
use Redkiwi\News\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class CategoryRepository implements CategoryRepositoryInterface {
    
    /**
     * Cached instances
     * 
     * @var array
     */
    protected $instances = [];
    
    /**
     * @var ResourceCategory
     */
    protected $resource;

    /**
     * @var CategoryInterface
     */
    protected $categoryInterfaceFactory;

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var Data\CategorySearchResultInterfaceFactory
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
     * @param ResourceCategory $resource
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param CategoryInterfaceFactory $categoryInterfaceFactory
     * @param CategorySearchResultInterfaceFactory $categorySearchResultsInterfaceFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceCategory $resource,
        CategoryCollectionFactory $categoryCollectionFactory,
        CategoryInterfaceFactory $categoryInterfaceFactory,
        CategorySearchResultInterfaceFactory $categorySearchResultsInterfaceFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource                 = $resource;
        $this->categoryCollectionFactory    = $categoryCollectionFactory;
        $this->categoryInterfaceFactory     = $categoryInterfaceFactory;
        $this->searchResultsFactory     = $categorySearchResultsInterfaceFactory;
        $this->dataObjectHelper         = $dataObjectHelper;
        $this->dataObjectProcessor      = $dataObjectProcessor;
        $this->storeManager             = $storeManager;
    }

    /**
     * Save data
     *
     * @param CategoryInterface $category
     * @return CategoryInterface
     * @throws CouldNotSaveException
     */
    public function save(CategoryInterface $category) {
        if (!$category->getStores()) {
            $storeId = 0; // all stores
            $category->setStores($storeId);
        }
        try {
            $this->resource->save($category);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the category: %1',
                $exception->getMessage()
            ));
        }
        return $category;
    }
    
    /**
     * Load category data by given id
     *
     * @param int $id
     * @return CategoryInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id) {
        if (!isset($this->instances[$id])) {
            $category = $this->categoryInterfaceFactory->create();
            $category->load($id);
            if (!$category->getId()) {
                throw new NoSuchEntityException(__('News category with id "%1" does not exist.', $id));
            }
            $this->instances[$id] = $category;
        }
        return $this->instances[$id];
    }
    
    /**
     * Load data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return CategorySearchResultInterface
     */
    public function getList(SearchCriteriaInterface $criteria) {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->categoryCollectionFactory->create();        
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
        $category = [];
        /** @var CategoryInterface $categoryModel */
        foreach ($collection as $categoryModel) {
            $categoryDataObject = $this->categoryInterfaceFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $categoryDataObject,
                $categoryModel->getData(),
                CategoryInterface::class
            );
            $category[] = $categoryDataObject;
        }
        $searchResults->setItems($category);
        return $searchResults;
    }
    
    /**
     * Delete category
     *
     * @param CategoryInterface $category
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(CategoryInterface $category) {
        $id = $category->getId();
        try {
            if (isset($this->instances[$id])) {
                unset($this->instances[$id]);
            }
            $this->resource->delete($category);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the category: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * Delete category by given Identity
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
