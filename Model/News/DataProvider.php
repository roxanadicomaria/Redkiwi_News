<?php
namespace Redkiwi\News\Model\News;

use Redkiwi\News\Model\ResourceModel\News\CollectionFactory;
use Magento\Framework\Registry;
use Magento\Framework\App\RequestInterface;
use Redkiwi\News\Api\NewsRepositoryInterface;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider {
    
    /**
     * @var \Redkiwi\News\Model\ResourceModel\News\Collection
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
     * @var NewsRepositoryInterface
     */
    protected $newsRepository;

    /**
     * 
     * @param type $name
     * @param type $primaryFieldName
     * @param type $requestFieldName
     * @param CollectionFactory $newsCollectionFactory
     * @param Registry $registry
     * @param RequestInterface $request
     * @param NewsRepositoryInterface $newsRepository
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $newsCollectionFactory,
        Registry $registry,
        RequestInterface $request,
        NewsRepositoryInterface $newsRepository, 
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $newsCollectionFactory->create();
        $this->registry = $registry;
        $this->request = $request;
        $this->newsRepository = $newsRepository;
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
        $item = $this->getCurrentNews();
        if ($item && !empty($item->getData())) {
            // attach categories to news data
            $item->getCategories();
            $itemData = $item->getData();  
            // prepare image data
            if (isset($itemData['image'])) {
                unset($itemData['image']);
                $itemData['image'][0]['name'] = $item->getData('image');
                $itemData['image'][0]['url'] = $item->getImageUrl();
            }
            // prepare file data
            if (isset($itemData['file'])) {
                unset($itemData['file']);
                $itemData['file'][0]['name'] = $item->getData('file');
                $itemData['file'][0]['url'] = $item->getFileUrl();
            }
            $this->loadedData[$item->getId()] = $itemData;
        }
        return $this->loadedData;        
    }
    
    /**
     * Get current news
     *
     * @return News
     * @throws NoSuchEntityException
     */
    public function getCurrentNews() {
        $item = $this->registry->registry('news');
        if ($item) {
            return $item;
        }
        $requestId = $this->request->getParam($this->requestFieldName);
        if ($requestId) {            
            $modelRepository = $this->newsRepository;
            $model = $modelRepository->getById($requestId);                
            if (!$model->getId()) {
                throw NoSuchEntityException::singleField('id', $requestId);
            }
        }
        return $model;
    }
    
}
