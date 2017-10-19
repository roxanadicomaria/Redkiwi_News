<?php
namespace Redkiwi\News\Controller\Adminhtml\Category;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Redkiwi\News\Api\Data\CategoryInterfaceFactory;
use Redkiwi\News\Api\CategoryRepositoryInterface;

class Save extends \Magento\Backend\App\Action {
           
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Redkiwi_News::save_category';
    
    /**
     * News repository
     * 
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;
    
    /**
     * News factory
     * 
     * @var CategoryInterfaceFactory
     */
    protected $categoryFactory;
    
    /**
     * @var PostDataProcessor
     */
    protected $dataProcessor;
    
    /**
     * 
     * @param Context $context
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CategoryInterfaceFactory $categoryFactory
     * @param \Redkiwi\News\Controller\Adminhtml\News\PostDataProcessor $dataProcessor
     */
    public function __construct(
        Context $context,
        CategoryRepositoryInterface $categoryRepository,
        CategoryInterfaceFactory $categoryFactory,
        PostDataProcessor $dataProcessor
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->categoryFactory = $categoryFactory;
        $this->dataProcessor = $dataProcessor;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute() {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();        
        if ($data) {
            $data = $this->dataProcessor->filter($data);
            $id = $this->getRequest()->getParam('id');
            if (empty($data['id'])) {
                unset($data['id']);
            }
            /** @var \Redkiwi\News\Model\News $model */            
            if ($id) {
                $model = $this->categoryRepository->getById((int)$id);  
                if (!$model->getId() && $id) {
                    $this->messageManager->addError(__('This category no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
            } else {
                $model = $this->categoryFactory->create();
            }
            // check and fill URL key
            $data = $this->_checkUrlKey($data);
            // attach data to model
            $model->setData($data);  
            // prepare news data
            if (isset($data['category_news'])
                && is_string($data['category_news'])
            ) {
                $news = json_decode($data['category_news'], true);
                $model->setPostedNews($news);
            }
            if (!$this->dataProcessor->validate($data)) {
                return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
            }            
            try {
                $model->save();
                $this->messageManager->addSuccess(__('You saved the category.'));
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->_getSession()->setCategoryData($data);
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_getSession()->setCategoryData($data);
                $this->messageManager->addException($e, __('Something went wrong while saving the category.'));
            }
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
    
    /**
     * Check if URL key is empty, and if so convert title to URL key
     * 
     * @param array $data
     * @return array
     */
    protected function _checkUrlKey($data) {        
        // check if URL key is empty and convert title as URL key
        if (empty($data[\Redkiwi\News\Api\Data\NewsInterface::URL_KEY])) {
            $title = $data[\Redkiwi\News\Api\Data\NewsInterface::TITLE];
            $data[\Redkiwi\News\Api\Data\NewsInterface::URL_KEY] = strtolower(urlencode(str_replace([' '], ['-'], $title)));
        }
        return $data;
    }
    
}
