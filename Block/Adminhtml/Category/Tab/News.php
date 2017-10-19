<?php
namespace Redkiwi\News\Block\Adminhtml\Category\Tab;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;
use Redkiwi\News\Model\NewsFactory;
use Redkiwi\News\Model\News\Source\Status;

class News extends \Magento\Backend\Block\Widget\Grid\Extended {
    
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry = null;

    /**
     * @var NewsFactory
     */
    protected $newsFactory;
    
    /**
     * @var Status
     */
    protected $_status;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param NewsFactory $newsFactory
     * @param Registry $coreRegistry
     * @param Status $status
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        NewsFactory $newsFactory,
        Registry $coreRegistry,
        Status $status,
        array $data = []
    ) {
        $this->newsFactory = $newsFactory;
        $this->coreRegistry = $coreRegistry;
        $this->_status = $status;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct() {
        parent::_construct();
        $this->setId('redkiwi_news_category_news');
        $this->setDefaultSort('position');
        $this->setDefaultDir('asc');
        $this->setUseAjax(true);
    }

    /**
     * @return array|null
     */
    public function getCategory() {
        return $this->coreRegistry->registry('category');
    }

    /**
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column) {        
        // Set custom filter for in category flag
        if ($column->getId() == 'in_category') {
            $newsIds = $this->_getSelectedNews();
            if (empty($newsIds)) {
                $newsIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('main_table.id', ['in' => $newsIds]);
            } elseif (!empty($newsIds)) {
                $this->getCollection()->addFieldToFilter('main_table.id', ['nin' => $newsIds]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection() {
        if ($this->getCategory()->getId()) {
            $this->setDefaultFilter(['in_category' => 1]);
        }
        $collection = $this->newsFactory->create()->getCollection();
        $collection->getSelect()->joinLeft(                    
                    \Redkiwi\News\Setup\InstallSchema::NEWS_CATEGORIES_ITEMS_TABLE,
                    'news_id=main_table.id AND category_id='.(int)$this->getRequest()->getParam('id', 0),
                    'position'
                ); // add position to collection
        $this->setCollection($collection);        
        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns() {        
        $this->addColumn(
            'in_category',
            [
                'type' => 'checkbox',
                'name' => 'in_category',
                'values' => $this->_getSelectedNews(),
                'index' => 'id',
                'header_css_class' => 'col-select col-massaction',
                'column_css_class' => 'col-select col-massaction'
            ]
        );        
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'title', 
            [
                'header' => __('Title'), 
                'index' => 'title'
            ]
        ); 
        $this->addColumn(
            'url_key', 
            [
                'header' => __('URL key'), 
                'index' => 'url_key'
            ]
        ); 
        $this->addColumn(
            'author', 
            [
                'header' => __('Author'), 
                'index' => 'author'
            ]
        ); 
        $this->addColumn(
            'status', 
            [
                'type' => 'options',
                'options' => $this->_status->getOptionArray(),
                'header' => __('Status'), 
                'index' => 'status'
            ]
        ); 
        $this->addColumn(
            'publish_at', 
            [
                'header' => __('Publish At'), 
                'index' => 'publish_at'
            ]
        ); 
        $this->addColumn(
            'position',
            [
                'header' => __('Position'),
                'type' => 'number',
                'index' => 'position',
                'editable' => true
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl() {
        return $this->getUrl('redkiwi_news/*/grid', ['_current' => true]);
    }

    /**
     * @return array
     */
    protected function _getSelectedNews() {
        $news = $this->getRequest()->getPost('selected_news');
        if ($news === null) {
            $news = $this->getCategory()->getNewsPosition();
            return array_keys($news);
        }
        return $news;
    }
    
}
