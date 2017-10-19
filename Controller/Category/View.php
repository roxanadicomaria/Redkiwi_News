<?php
namespace Redkiwi\News\Controller\Category;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Redkiwi\News\Api\CategoryRepositoryInterface;

class View extends Action {
    
    /**
     * stores configurations paths
     */
    const CONFIG_PATH_URL_KEY = 'redkiwi_news/general/url_key';
    const CONFIG_PATH_TITLE = 'redkiwi_news/general/title';
    const CONFIG_PATH_META_TITLE = 'redkiwi_news/seo/title';
    
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @var \Redkiwi\News\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;
    
    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;
    
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * 
     * @param Context $context
     * @param CategoryRepositoryInterface $categoryRepository
     * @param ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     * @param Registry $coreRegistry
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        CategoryRepositoryInterface $categoryRepository,
        ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        Registry $coreRegistry,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->categoryRepository = $categoryRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Forward|\Magento\Framework\View\Result\Page
     */
    public function execute() {
        try {
            $id = (int)$this->getRequest()->getParam('id');
            $category = $this->categoryRepository->getById($id);
            if (!$category || !$category->getStatus()) {
                // no category or category is not active
                throw new \Exception();
            }
            // get current store ID and category stores
            $currentStoreId = $this->storeManager->getStore()->getId();
            $categoryStores = explode(',', $category->getStores());
            // check if category is assigned to current store
            if (!in_array(\Magento\Store\Model\Store::DEFAULT_STORE_ID, $categoryStores) && !in_array($currentStoreId, $categoryStores)) {
                // category is not assigned to current store
                throw new \Exception();
            }
        } catch (\Exception $e) {
            // if category cannot be seen redirect to 404 page
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');
            return $resultForward;
        }
        
        // set category item in registry
        $this->coreRegistry->register('current_category', $category);
        $resultPage = $this->resultPageFactory->create();
        // set meta data
        $resultPage->getConfig()->getTitle()->set(
            $category->getTitle() . ' | ' . $this->scopeConfig->getValue(self::CONFIG_PATH_META_TITLE, ScopeInterface::SCOPE_STORE)
        );
        $resultPage->getConfig()->setDescription(
            $category->getMetaDescription()
        );
        $resultPage->getConfig()->setKeywords(
            $category->getMetaKeywords()
        );
        /** set page title */
        $pageMainTitle = $resultPage->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($category->getTitle());
        }
        
        /** @var \Magento\Theme\Block\Html\Breadcrumbs $breadcrumbsBlock */
        $breadcrumbsBlock = $resultPage->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbsBlock) {
            $breadcrumbsBlock->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title'    => __('Home'),
                    'link'  => $this->_url->getUrl('')
                ]
            );
            $breadcrumbsBlock->addCrumb(
                'news',
                [
                    'label' => $this->scopeConfig->getValue(self::CONFIG_PATH_TITLE, ScopeInterface::SCOPE_STORE),
                    'title' => $this->scopeConfig->getValue(self::CONFIG_PATH_TITLE, ScopeInterface::SCOPE_STORE),
                    'link'  => $this->_url->getUrl(trim($this->scopeConfig->getValue(self::CONFIG_PATH_URL_KEY, ScopeInterface::SCOPE_STORE)))
                ]
            );
            $breadcrumbsBlock->addCrumb(
                'news-'.$category->getId(),
                [
                    'label' => $category->getTitle(),
                    'title' => $category->getTitle()
                ]
            );
        }
        
        return $resultPage;
    }    
}
