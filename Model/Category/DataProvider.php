<?php
namespace Redkiwi\News\Model\Category;

use Redkiwi\News\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Registry;
use Magento\Framework\App\RequestInterface;
use Redkiwi\News\Api\CategoryRepositoryInterface;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider {
    
    /**
     * @var \Redkiwi\News\Model\ResourceModel\Category\Collection
     */
    protected $collection;

    /**
     * @var array
     */
    protected $loadedData;
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;
    
    /**
     * News repository
     * 
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * 
     * @param type $name
     * @param type $primaryFieldName
     * @param type $requestFieldName
     * @param CollectionFactory $categoryCollectionFactory
     * @param Registry $registry
     * @param RequestInterface $request
     * @param CategoryRepositoryInterface $categoryRepository
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $categoryCollectionFactory,
        Registry $registry,
        RequestInterface $request,
        CategoryRepositoryInterface $categoryRepository, 
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $categoryCollectionFactory->create();
        $this->registry = $registry;
        $this->request = $request;
        $this->categoryRepository = $categoryRepository;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData() {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $item = $this->getCurrentCategory();
        if ($item && !empty($item->getData())) {
            $itemData = $item->getData();            
            $this->loadedData[$item->getId()] = $itemData;
        }
        return $this->loadedData;        
    }
    
    /**
     * Get current category
     *
     * @return Category
     * @throws NoSuchEntityException
     */
    public function getCurrentCategory() {
        $item = $this->registry->registry('category');
        if ($item) {
            return $item;
        }
        $requestId = $this->request->getParam($this->requestFieldName);
        if ($requestId) {            
            $modelRepository = $this->categoryRepository;
            $model = $modelRepository->getById($requestId);                
            if (!$model->getId()) {
                throw NoSuchEntityException::singleField('id', $requestId);
            }
        }
        return $model;
    }
    
}
