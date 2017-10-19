<?php
namespace Redkiwi\News\Block\Adminhtml\Category\Widget;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Framework\View\Model\PageLayout\Config\BuilderInterface;
use Redkiwi\News\Model\Category;
use Redkiwi\News\Model\CategoryFactory;
use Redkiwi\News\Model\ResourceModel\Category\CollectionFactory;
use Redkiwi\News\Model\News\Source\Status;

class Chooser extends Extended {
    
    /**
     * @var Category
     */
    protected $category;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var BuilderInterface
     */
    protected $pageLayoutBuilder;
    
    /**
     * @var Status
     */
    protected $status;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param Category $category
     * @param CategoryFactory $categoryFactory
     * @param CollectionFactory $collectionFactory
     * @param BuilderInterface $pageLayoutBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        Category $category,
        CategoryFactory $categoryFactory,
        CollectionFactory $collectionFactory,
        BuilderInterface $pageLayoutBuilder,
        Status $status,
        array $data = []
    ) {
        $this->pageLayoutBuilder = $pageLayoutBuilder;
        $this->category = $category;
        $this->categoryFactory = $categoryFactory;
        $this->collectionFactory = $collectionFactory;
        $this->status = $status;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Block construction, prepare grid params
     *
     * @return void
     */
    protected function _construct() {
        parent::_construct();
        $this->setUseAjax(true);
        $this->setDefaultFilter(['chooser_is_active' => '1']);
    }

    /**
     * Prepare chooser element HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element Form Element
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function prepareElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
        $uniqId = $this->mathRandom->getUniqueHash($element->getId());
        $sourceUrl = $this->getUrl('redkiwi_news/news_widget/chooser', ['uniq_id' => $uniqId]);
        $chooser = $this->getLayout()->createBlock(
            'Magento\Widget\Block\Adminhtml\Widget\Chooser'
        )->setElement(
            $element
        )->setConfig(
            $this->getConfig()
        )->setFieldsetId(
            $this->getFieldsetId()
        )->setSourceUrl(
            $sourceUrl
        )->setUniqId(
            $uniqId
        );
        if ($element->getValue()) {
            $category = $this->categoryFactory->create()->load((int)$element->getValue());
            if ($category->getId()) {
                $chooser->setLabel($this->escapeHtml($category->getTitle()));
            }
        }
        $element->setData('after_element_html', $chooser->toHtml());
        return $element;
    }

    /**
     * Grid Row JS Callback
     *
     * @return string
     */
    public function getRowClickCallback() {
        $chooserJsObject = $this->getId();
        $js = '
            function (grid, event) {
                var trElement = Event.findElement(event, "tr");
                var categoryTitle = trElement.down("td").next().innerHTML;
                var categoryId = trElement.down("td").innerHTML.replace(/^\s+|\s+$/g,"");
                ' .
            $chooserJsObject .
            '.setElementValue(categoryId);
                ' .
            $chooserJsObject .
            '.setElementLabel(categoryTitle);
                ' .
            $chooserJsObject .
            '.close();
            }
        ';
        return $js;
    }

    /**
     * Prepare collection
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareCollection() {
        $collection = $this->collectionFactory->create();
        /* @var $collection \Redkiwi\News\Model\ResourceModel\Category\CollectionFactory */
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns for grid
     *
     * @return $this
     */
    protected function _prepareColumns() {
        $this->addColumn(
            'chooser_id',
            [
                'header' => __('ID'),
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'chooser_title',
            [
                'header' => __('Title'),
                'index' => 'title',
                'header_css_class' => 'col-title',
                'column_css_class' => 'col-title'
            ]
        );

        $this->addColumn(
            'chooser_identifier',
            [
                'header' => __('URL Key'),
                'index' => 'url_key',
                'header_css_class' => 'col-url',
                'column_css_class' => 'col-url'
            ]
        );

        $this->addColumn(
            'chooser_is_active',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->status->getOptionArray(),
                'header_css_class' => 'col-status',
                'column_css_class' => 'col-status'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl() {
        return $this->getUrl('redkiwi_news/news_widget/chooser', ['_current' => true]);
    }
    
}
