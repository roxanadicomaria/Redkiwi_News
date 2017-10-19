<?php
namespace Redkiwi\News\Controller\Adminhtml\News;

use Redkiwi\News\Controller\Adminhtml\News;

class Index extends News {
    
    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute() {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->getConfig()->getTitle()->prepend(__('News'));
        return $resultPage;
    }
    
}
