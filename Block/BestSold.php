<?php
namespace Redkiwi\News\Block;

use Magento\Framework\Registry;
use Magento\Framework\Api\SortOrder;
use Magento\Store\Model\StoreManagerInterface;

class BestSold extends \Magento\Catalog\Block\Product\AbstractProduct {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param Registry $coreRegistry
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param array $data
     */
    public function __construct(
            \Magento\Catalog\Block\Product\Context $context, 
            Registry $coreRegistry, 
            StoreManagerInterface $storeManager, 
            \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory, 
            \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility, 
            \Magento\Framework\Url\Helper\Data $urlHelper, 
            array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_storeManager = $storeManager;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->urlHelper = $urlHelper;
        parent::__construct($context, $data);
    }

    /**
     * Prepare and return product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getBestSoldItems() {
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->_productCollectionFactory->create();
        // add visibility filter
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());         
        // add listing attributes and prices to collection
        $collection = $this->_addProductAttributesAndPrices($collection)
                ->addStoreFilter();
        // join with best sold indexer table
        $collection->getSelect()->joinLeft(                    
                    ['bestsellers' => \Redkiwi\News\Setup\InstallSchema::NEWS_BEST_SOLD_PRODUCTS_TABLE],
                    'bestsellers.product_id = e.entity_id AND bestsellers.store_id = ' . $this->_storeManager->getStore()->getId(),
                    ['qty_sold']
                )
                ->order('bestsellers.qty_sold '. SortOrder::SORT_DESC)
                ->limit(10);
        // return collection
        return $collection->load();
    }
        
}
