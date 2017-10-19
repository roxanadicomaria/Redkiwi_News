<?php
namespace Redkiwi\News\Block\System\Config\Form\Field;

class Cmslist extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray {
    
    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $_elementFactory;

    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $_cmsPageFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory
     * @param \Magento\Cms\Model\PageFactory $cmsPageFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        \Magento\Cms\Model\PageFactory $cmsPageFactory,
        array $data = []
    ) {
        $this->_elementFactory = $elementFactory;
        $this->_cmsPageFactory = $cmsPageFactory;
        parent::__construct($context, $data);
    }

    /**
     * Initialise form fields
     *
     * @return void
     */
    protected function _construct() {
        $this->addColumn('title', ['label' => __('Title')]);
        $this->addColumn('link', ['label' => __('CMS Page')]);
        $this->addColumn('sort', ['label' => __('Sort Order')]);
        $this->_addButtonLabel = __('Add');
        $this->_addAfter = false;        
        parent::_construct();
    }

    /**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     * @return string
     */
    public function renderCellTemplate($columnName) {
        if ($columnName == 'link' && isset($this->_columns[$columnName])) {
            /** @var $label \Magento\Cms\Model\PageFactory */
            $pages = $this->_cmsPageFactory->create()->getCollection();
            $options = [
                ['value' => '', 'label' => __('-- No Page --')]
            ];
            foreach ($pages as $page) {
                $options[] = ['value' => $page->getPageId(), 'label' => $page->getTitle()];
            }
            $element = $this->_elementFactory->create('select');
            $element->setForm(
                $this->getForm()
            )->setName(
                $this->_getCellInputElementName($columnName)
            )->setHtmlId(
                $this->_getCellInputElementId('<%- _id %>', $columnName)
            )->setValues(
                $options
            );
            return str_replace("\n", '', $element->getElementHtml());
        }

        return parent::renderCellTemplate($columnName);
    }    
}
