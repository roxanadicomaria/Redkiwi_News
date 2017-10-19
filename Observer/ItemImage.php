<?php
namespace Redkiwi\News\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ItemImage implements ObserverInterface {
    
    /**
     * stores configurations paths
     */
    const CONFIG_PATH_DEFAULT_IMAGE = 'redkiwi_news/general/default_image';
    
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }
    
    /**
     * Set default image
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $items = $observer->getEvent()->getNewsCollection();
        $defaultImage = $this->getDefaultImage();
        if ($defaultImage) {
            foreach ($items as $item) {
                if (!$item->getImage()) {
                    $item->setImage($defaultImage);
                }
            }
        }
    }
    
    /**
     * Get default image
     * 
     * @return string OR boolean
     */
    public function getDefaultImage() {
        $defaultImageValue = trim($this->scopeConfig->getValue(self::CONFIG_PATH_DEFAULT_IMAGE, ScopeInterface::SCOPE_STORE));
        if ($defaultImageValue) {
            return '/' . trim($defaultImageValue, '/');
        }
        return false;
    }

}
