<?php
namespace Redkiwi\News\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\Page;
use Redkiwi\News\Api\CategoryRepositoryInterface;
use Redkiwi\News\Model\Category as CategoryModel;

abstract class Category extends Action {
    
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Redkiwi_News::news';

    /**
     * @var Registry
     */
    protected $coreRegistry;
    
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * Category repository
     * 
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;
    
    /**
     * Category model
     * 
     * @var Category
     */
    protected $category;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Category $category
     */
    public function __construct(
            Context $context, 
            Registry $coreRegistry,
            PageFactory $resultPageFactory,
            CategoryRepositoryInterface $categoryRepository,
            CategoryModel $category
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->categoryRepository = $categoryRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->category = $category;
        parent::__construct($context);
    }

    /**
     * Init page
     *
     * @param Page $resultPage
     * @return Page
     */
    protected function initPage($resultPage) {
        $resultPage->setActiveMenu('Redkiwi_News::news')
            ->addBreadcrumb(__('Redkiwi'), __('Redkiwi'))
            ->addBreadcrumb(__('News'), __('News'))
            ->addBreadcrumb(__('Category'), __('Category'));
        return $resultPage;
    }
    
}
