<?php
namespace Redkiwi\News\Cron;

use Redkiwi\News\Helper\Image;

class FlushCache {    
    
    /**
     * @var \Redkiwi\News\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @param \Image $imageHelper
     */
    public function __construct(Image $imageHelper) {
        $this->_imageHelper = $imageHelper;
    }

    /**
     * This method is called from cron process, cron is working in UTC time
     *
     * @return void
     */
    public function execute() {
        $this->_imageHelper->flushImagesCache();
    }
    
}
