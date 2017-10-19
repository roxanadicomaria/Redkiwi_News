<?php
namespace Redkiwi\News\Controller\Adminhtml\Category;

use Redkiwi\News\Controller\Adminhtml\Category;

class Index extends Category {
    
    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute() {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->getConfig()->getTitle()->prepend(__('News Categories'));
        return $resultPage;
    }
    
}
