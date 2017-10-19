<?php
namespace Redkiwi\News\Model\Config\Backend;

class Image extends \Magento\Config\Model\Config\Backend\Image {
    
    /**
     * @return string[]
     */
    protected function _getAllowedExtensions() {
        return ['tif', 'tiff', 'png', 'jpg', 'jpe', 'jpeg', 'gif'];
    }
    
}
