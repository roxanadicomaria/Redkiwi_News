<?php
namespace Redkiwi\News\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Framework\ObjectManagerInterface;
use Redkiwi\News\Helper\Image as ImageHelper;

class Gallery extends Template {

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \ImageHelper
     */
    protected $_imageHelper;
    
     /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Registry $coreRegistry
     * @param ImageHelper $imageHelper
     * @param ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
            Template\Context $context, 
            Registry $coreRegistry, 
            ImageHelper $imageHelper,
            ObjectManagerInterface $objectManager,
            array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_imageHelper = $imageHelper;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $data);
    }

    /**
     * Get item information from register which saved in controller
     *
     * @return \Redkiwi\News\Model\News
     */
    public function getItemInformation() {
        return $this->_coreRegistry->registry('current_news');
    }
    
    /**
     * Get gallery data
     * 
     * @return array|boolean
     */
    public function getGalleryItems() {
        $item = $this->getItemInformation();
        $galleryData = [];
        if ($item->getGallery()) {
            // unserialize data
            $gallery = unserialize($item->getGallery());
            if (count($gallery)) {
                // prepare gallery data as array of objects
                foreach ($gallery as $item) {
                    // remove hidden items from gallery data
                    if (!isset($item['visible']) || !$item['visible']) {
                        continue;
                    }
                    // add item array to data object
                    $itemObject = $this->_objectManager->create('Magento\Framework\DataObject')->addData($item);
                    // add object to gallery array
                    $galleryData[] = $itemObject;
                }
                return $galleryData;
            }
        }
        return false;
    }
    
    /**
     * Return URL for resized image
     * 
     * @param Redkiwi\News\Model\News $item
     * @param integer $width
     * @param integer $height
     * @return string|false
     */
    public function getImageUrl($item, $width, $height = '') {
        return $this->_imageHelper->resize($item->getImage(), $width, $height);
    }
    
}
