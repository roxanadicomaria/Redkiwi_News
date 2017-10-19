<?php
namespace Redkiwi\News\Controller\Adminhtml\News;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Redkiwi\News\Helper\Image as ImageHelper;

class Flush extends \Magento\Backend\App\Action {
    
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Redkiwi_News::news';
    
    /**
     * @var ImageHelper
     */
    protected $imageHelper;
 
    /**
     * @param Context $context
     * @param \Redkiwi\News\Helper\Image $imageHelper
     */
    public function __construct(
        Context $context,
        ImageHelper $imageHelper
    ) {
        parent::__construct($context);
        $this->imageHelper = $imageHelper;
    }
 
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute() {
        if ($this->imageHelper->flushImagesCache()) {
            $this->messageManager->addSuccess(__('Images cache succesfully flushed.'));
        } else {
            $this->messageManager->addError(__('There was an error during flushing cache.'));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
        
}
