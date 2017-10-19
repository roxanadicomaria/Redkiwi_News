<?php
namespace Redkiwi\News\Controller\Item;
 
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Redkiwi\News\Api\NewsRepositoryInterface;
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
     * @var \Redkiwi\News\Api\NewsRepositoryInterface
     */
    protected $newsRepository;
    
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
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;
    
    /**
     * 
     * @param Context $context
     * @param NewsRepositoryInterface $newsRepository
     * @param ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     * @param Registry $coreRegistry
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        Context $context,
        NewsRepositoryInterface $newsRepository,
        ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        Registry $coreRegistry,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->newsRepository = $newsRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->categoryRepository = $categoryRepository;
        parent::__construct($context);
    }
 
    /**
     * @return \Magento\Framework\Controller\Result\Forward|\Magento\Framework\View\Result\Page
     */
    public function execute() {
        try {
            $id = (int)$this->getRequest()->getParam('id');
            $news = $this->newsRepository->getById($id);
            if (!$news || !$news->getStatus()) {
                // no news or news is not active
                throw new \Exception();
            }
            // get current store ID and news stores
            $currentStoreId = $this->storeManager->getStore()->getId();
            $newsStores = explode(',', $news->getStores());
            // check if news is assigned to current store and published            
            if (!in_array(\Magento\Store\Model\Store::DEFAULT_STORE_ID, $newsStores) && !in_array($currentStoreId, $newsStores)) {
                // news is not assigned to current store
                throw new \Exception();
            }
            if (strtotime($news->getPublishAt()) > time()) {
                // news is not yet pusblished
                throw new \Exception();
            }
        } catch (\Exception $e) {
            // if news cannot be seen redirect to 404 page
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');
            return $resultForward;
        }
        
        // set news item in registry
        $this->coreRegistry->register('current_news', $news);
        /**
         * try to load category if there is a parameter
         */
        $category = null;
        if($catId = $this->getRequest()->getParam('catid')) {
            $category = $this->categoryRepository->getById($catId);
            if ($category && $category->getStatus()) {
                $urlPrefix = trim($this->scopeConfig->getValue(self::CONFIG_PATH_URL_KEY, ScopeInterface::SCOPE_STORE),'/');
                $categoryUrl = $this->_url->getUrl($urlPrefix . '/' . \Redkiwi\News\Controller\Router::CATEGORY_URL_PREFIX . '/' . $category->getUrlKey());
                // set category in registry
                $this->coreRegistry->register('current_category', $category);
            }
        }
        $resultPage = $this->resultPageFactory->create();
        // set meta data
        $resultPage->getConfig()->getTitle()->set(
            $news->getTitle() . ' | ' . $this->scopeConfig->getValue(self::CONFIG_PATH_META_TITLE, ScopeInterface::SCOPE_STORE)
        );
        $resultPage->getConfig()->setDescription(
            $news->getMetaDescription()
        );
        $resultPage->getConfig()->setKeywords(
            $news->getMetaKeywords()
        );
        /** set page title */
        $pageMainTitle = $resultPage->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($news->getTitle());
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
                    'title'    => $this->scopeConfig->getValue(self::CONFIG_PATH_TITLE, ScopeInterface::SCOPE_STORE),
                    'link'  => $this->_url->getUrl(trim($this->scopeConfig->getValue(self::CONFIG_PATH_URL_KEY, ScopeInterface::SCOPE_STORE)))
                ]
            );
            if ($category && $categoryUrl) {
                $breadcrumbsBlock->addCrumb(
                    'category',
                    [
                        'label' => $category->getTitle(),
                        'title'    => $category->getTitle(),
                        'link'  => $categoryUrl
                    ]
                );
            }

            $breadcrumbsBlock->addCrumb(
                'news-'.$news->getId(),
                [
                    'label' => $news->getTitle(),
                    'title' => $news->getTitle()
                ]
            );
        }
        
        return $resultPage;
    }
    
}
