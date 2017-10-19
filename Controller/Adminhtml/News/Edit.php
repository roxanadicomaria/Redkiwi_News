<?php
namespace Redkiwi\News\Controller\Adminhtml\News;

use Redkiwi\News\Controller\Adminhtml\News;

class Edit extends News {
    
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Redkiwi_News::edit_item';
    
    /**
     * Edit news page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute() {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('id');
        $model = $this->news;
        $modelRepository = $this->newsRepository;
        
        // 2. Initial checking
        if ($id) {
            $model = $modelRepository->getById($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This news no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

  /**
         * Check if there are data in session (if there was an exception on saving category)
         */
        $itemData = $this->_getSession()->getNewsData(true);
        if (is_array($itemData)) {      
            if (isset($itemData['image']['delete'])) {
                $itemData['image'] = null;
            }
            if (isset($itemData['file']['delete'])) {
                $itemData['file'] = null;
            } 
            $model->addData($itemData);
        }

        $this->coreRegistry->register('news', $model);

        // 3. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit News') : __('New News'),
            $id ? __('Edit News') : __('New News')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('News'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getTitle() : __('New News'));
        return $resultPage;
    }
    
}
