<?php
namespace Redkiwi\News\Block;
 
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Cms\Model\Template\FilterProvider;
use Redkiwi\News\Model\News;
 
class View extends Template {
    
    /**
     * stores configurations paths
     */
    const CONFIG_PATH_URL_KEY = 'redkiwi_news/general/url_key';
    const CONFIG_PATH_FACEBOOK_ID = 'redkiwi_news/general/facebook_id';
    const CONFIG_PATH_COMMENTS = 'redkiwi_news/general/comments';
    const CONFIG_PATH_SHARE_ON = 'redkiwi_news/general/share_on';
    
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
     * get current News
     *
     * @return \Redkiwi\News\Model\News
     */
    public function getCurrentNews() {
        return $this->coreRegistry->registry('current_news');
    }
    
    
    /**
     * Return publish date in locale format
     * 
     * @param News $news
     * @return string | false
     */
    public function getPublishDate(News $news) {
        if ($news->getPublishAt()) {
            return $this->dateTime->formatDate($news->getPublishAt(), false);
        }
        return false;
    }
    
     /**
     * Return current news URL
     * 
     * @return string
     */
    public function getCurrentLink() {        
        return $this->getUrl('news/item/view', ['id' => $this->getCurrentNews()->getId()]);
    }
 
    /**
     * Return current news URL - SEO friendly
     * 
     * @return string
     */
    public function getCurrentSeoLink() {       
        $urlPrefix = trim($this->scopeConfig->getValue(self::CONFIG_PATH_URL_KEY, ScopeInterface::SCOPE_STORE),'/');
        return $this->getUrl($urlPrefix . '/' . $this->getCurrentNews()->getUrlKey());
    }  
    
    /**
     * Check if comments are enabled
     * 
     * @return int
     */
    public function canComment() {
        return (int)$this->scopeConfig->getValue(self::CONFIG_PATH_COMMENTS, ScopeInterface::SCOPE_STORE);
    }
    
    /**
     * Check if share is possible on certain social platform
     * 
     * @param string $platform
     * @return boolean
     */
    public function canShareOn($platform){
        $shareOn = explode(',',$this->scopeConfig->getValue(self::CONFIG_PATH_SHARE_ON, ScopeInterface::SCOPE_STORE));
        if (in_array($platform, $shareOn)) {
            return true;
        }
        return false;
    }
    
    /**
     * Get Facebook App ID
     * 
     * @return string
     */
    public function getFacebookAppId() {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_FACEBOOK_ID, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Prepare HTML content
     * 
     * @param News $news
     * @return string
     */
    public function getContent(News $news) {
        $content = $this->filterProvider->getPageFilter()->filter($news->getContent());
        return $content;
    }

    
}
