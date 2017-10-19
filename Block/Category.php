<?php
namespace Redkiwi\News\Block;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Cms\Model\Template\FilterProvider;
use Redkiwi\News\Model\Category as CategoryModel;

class Category extends Template {
    
    /**
     * stores configurations paths
     */
    const CONFIG_PATH_URL_KEY = 'redkiwi_news/general/url_key';
    
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @var Registry
     */
    protected $coreRegistry;
        
    /**
     * @var DateTime
     */
    protected $dateTime;
    
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $filterProvider;
        
    /**
     * @param Context $context
     * @param Registry $registry
     * @param DateTime $dateTime
     * @param ScopeConfigInterface $scopeConfig
     * @param FilterProvider $filterProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        DateTime $dateTime,
        ScopeConfigInterface $scopeConfig,
        FilterProvider $filterProvider,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->dateTime = $dateTime;
        $this->scopeConfig = $scopeConfig;
        $this->filterProvider = $filterProvider;
        parent::__construct($context, $data);
    }

    /**
     * get current category
     *
     * @return \Redkiwi\News\Model\Category
     */
    public function getCurrentCategory() {
        return $this->coreRegistry->registry('current_category');
    }
        
    /**
     * Return current category URL
     * 
     * @return string
     */
    public function getCurrentLink() {        
        return $this->getUrl('news/category/view', ['id' => $this->getCurrentCategory()->getId()]);
    }
    
    /**
     * Return current category URL - SEO friendly
     * 
     * @return string
     */
    public function getCurrentSeoLink() {        
        $urlPrefix = trim($this->scopeConfig->getValue(self::CONFIG_PATH_URL_KEY, ScopeInterface::SCOPE_STORE),'/');
        return $this->getUrl($urlPrefix . '/' . $this->getCurrentCategory()->getUrlKey());
    }
    
    /**
     * Prepare HTML content
     * 
     * @param CategoryModel $category
     * @return string
     */
    public function getContent(CategoryModel $category) {
        $content = $this->filterProvider->getPageFilter()->filter($category->getContent());
        return $content;
    }    
}
