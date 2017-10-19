<?php
namespace Redkiwi\News\Controller\Index;
 
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
 
class Index extends Action {
    
    /**
     * stores configurations paths
     */
    const CONFIG_PATH_TITLE = 'redkiwi_news/general/title';
    const CONFIG_PATH_META_TITLE = 'redkiwi_news/seo/title';
    const CONFIG_PATH_META_DESCRIPTION = 'redkiwi_news/seo/description';
    const CONFIG_PATH_META_KEYWORDS = 'redkiwi_news/seo/keywords';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    
    /**
     * 
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }
 
    /**
     * Show a list with active news
     */
    public function execute() {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(
            $this->scopeConfig->getValue(self::CONFIG_PATH_META_TITLE, ScopeInterface::SCOPE_STORE)
        );
        $resultPage->getConfig()->setDescription(
            $this->scopeConfig->getValue(self::CONFIG_PATH_META_DESCRIPTION, ScopeInterface::SCOPE_STORE)
        );
        $resultPage->getConfig()->setKeywords(
            $this->scopeConfig->getValue(self::CONFIG_PATH_META_KEYWORDS, ScopeInterface::SCOPE_STORE)
        ); 
        /** set page title */
        $pageMainTitle = $resultPage->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {            
            $pageMainTitle->setPageTitle($this->scopeConfig->getValue(self::CONFIG_PATH_TITLE, ScopeInterface::SCOPE_STORE));
        }
        /** @var \Magento\Theme\Block\Html\Breadcrumbs $breadcrumbsBlock */
        $breadcrumbsBlock = $resultPage->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbsBlock) {
            $breadcrumbsBlock->addCrumb(
                'home',
                [
                    'label'    => __('Home'),
                    'title'    => __('Home'),
                    'link'     => $this->_url->getUrl('')
                ]
            );
            $breadcrumbsBlock->addCrumb(
                'news',
                [
                    'label'    => $this->scopeConfig->getValue(self::CONFIG_PATH_TITLE, ScopeInterface::SCOPE_STORE),
                    'title'    => $this->scopeConfig->getValue(self::CONFIG_PATH_TITLE, ScopeInterface::SCOPE_STORE)
                ]
            );
        }
        return $resultPage;        
    }    
}
