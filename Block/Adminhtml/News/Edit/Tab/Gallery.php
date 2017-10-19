<?php
namespace Redkiwi\News\Block\Adminhtml\News\Edit\Tab;

use Magento\Backend\Block\Widget;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

class Gallery extends Widget implements RendererInterface {
    
    /**
     * @var string
     */
    protected $_template = 'Redkiwi_News::news/edit/gallery.phtml';
    
    /**
     * Form element instance
     *
     * @var \Magento\Framework\Data\Form\Element\AbstractElement
     */
    protected $_element;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }
    
    /**
     * Retrieve attribute option values
     *
     * @return array
     */
    public function getOptionValues() {
        /** @var $model \Redkiwi\News\Model\News */
        $model = $this->_coreRegistry->registry('news');
        $values = $model->getData('gallery');
        if ($values === null || $values == '') {
            $values = [];
        } else {
            // unserialize values
            $values = unserialize($values);
        }        
        foreach ($values as $key => $value) {
            if (isset($value['image'])) {
                $imageName = $value['image'];
                $url = $model->getImageUrl($imageName);
                $values[$key]['image_url'] = $url;
            }
        }
        return $values;
    }
    
    /**
     * Return max of options ID keys
     * 
     * @param array $options
     * @return int
     */
    public function getLastKey($options) {
        $keys = [];
        foreach ($options as $option) {
            $keys[] = (int)str_replace('option_', '', $option['id']);
        }
        if (count($keys)) {
            return max($keys);
        } else {
            return 0;
        }
    }
    
    /**
     * Render HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
        $this->setElement($element);
        return $this->toHtml();
    }

    /**
     * Set form element instance
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Price\Group\AbstractGroup
     */
    public function setElement(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
        $this->_element = $element;
        return $this;
    }

    /**
     * Retrieve form element instance
     *
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getElement() {
        return $this->_element;
    }
    
}
