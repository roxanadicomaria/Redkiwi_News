<?php
namespace Redkiwi\News\Controller\Adminhtml\Category;

use Redkiwi\News\Controller\Adminhtml\Category;

class Edit extends Category {
    
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Redkiwi_News::edit_category';
    
    /**
     * Edit category page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute() {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('id');
        $model = $this->category;
        $modelRepository = $this->categoryRepository;
        
        // 2. Initial checking
        if ($id) {
            $model = $modelRepository->getById($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This category no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        
        /**
         * Check if there are data in session (if there was an exception on saving category)
         */
        $itemData = $this->_getSession()->getCategoryData(true);
        if (is_array($itemData)) {
            $model->addData($itemData);
        }        
        $this->coreRegistry->register('category', $model);

        // 3. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit News') : __('New Category'),
            $id ? __('Edit News') : __('New Category')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Category'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getTitle() : __('New Category'));
        return $resultPage;
    }
    
}
