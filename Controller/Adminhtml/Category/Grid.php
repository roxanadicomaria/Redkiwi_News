<?php
namespace Redkiwi\News\Controller\Adminhtml\Category;

use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Redkiwi\News\Api\CategoryRepositoryInterface;
use Redkiwi\News\Model\Category as CategoryModel;
use Redkiwi\News\Controller\Adminhtml\Category;

class Grid extends Category {
    
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        CategoryRepositoryInterface $categoryRepository,
        CategoryModel $category
    ) {
        parent::__construct($context, $coreRegistry, $resultPageFactory, $categoryRepository, $category);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * Grid Action
     * Display list of news related to current category
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute() {
        $category = $this->_initCategory();
        if (!$category) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('redkiwi_news/*/', ['_current' => true, 'id' => null]);
        }
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents(
            $this->layoutFactory->create()->createBlock(                
                'Redkiwi\News\Block\Adminhtml\Category\Tab\News',
                'category.news.grid'
            )->toHtml()
        );
    }
    
    /**
     * Return category model
     * 
     * @return CategoryModel
     */
    protected function _initCategory() {
        $id = $this->getRequest()->getParam('id');
        $model = $this->category;
        $modelRepository = $this->categoryRepository;        
        //Initial checking
        if ($id) {
            $model = $modelRepository->getById($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This category no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('redkiwi_news/*/');
            }
        }
        // Register model to use later in blocks
        $this->coreRegistry->register('category', $model);
        return $model;
    }    
}
