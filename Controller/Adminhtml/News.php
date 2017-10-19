<?php
namespace Redkiwi\News\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\Page;
use Redkiwi\News\Api\NewsRepositoryInterface;
use Redkiwi\News\Model\News as NewsModel;

abstract class News extends Action {
    
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
     * News repository
     * 
     * @var NewsRepositoryInterface
     */
    protected $newsRepository;

    /**
     * News model
     * 
     * @var News
     */
    protected $news;

    /**
     * 
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param NewsRepositoryInterface $newsRepository
     * @param NewsModel $news
     */
    public function __construct(
            Context $context, 
            Registry $coreRegistry,
            PageFactory $resultPageFactory,
            NewsRepositoryInterface $newsRepository,
            NewsModel $news
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->newsRepository = $newsRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->news = $news;
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
            ->addBreadcrumb(__('News'), __('News'));
        return $resultPage;
    }
    
}
