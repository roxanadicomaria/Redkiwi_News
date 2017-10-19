<?php
namespace Redkiwi\News\Block;
 
use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Cms\Model\PageFactory;
 
class Cmslist extends Template {
    
    /**
     * stores configurations paths
     */
    const CONFIG_PATH_CMS_LIST = 'redkiwi_news/cms/cms_list';
    const CONFIG_PATH_CMS_TITLE = 'redkiwi_news/cms/title';
    
    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $pageFactory;
 
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param PageFactory $pageFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PageFactory $pageFactory,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->pageFactory = $pageFactory;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }
 
    /**
     * Set items collection
     */
    protected  function _construct() {
        parent::_construct();
        // get CMS pages list from configuration
        $list = $this->getCmsPageList();
        // un-serialize the config data
        $unserializedData = unserialize($list);
        $collection = $this->sortArray($unserializedData, 'sort');
        $this->setCollection($collection);
    }

    /**
     * Get CMS pages list
     *
     * @return string
     */
    public function getCmsPageList() {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_CMS_LIST,
            ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Get box title
     *
     * @return string
     */
    public function getBoxTitle() {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_CMS_TITLE,
            ScopeInterface::SCOPE_STORE
        );
    }
  
    /**
     * Return item URL - it's the attached CMS page link
     * 
     * @param array $item
     * @return string
     */
    public function getItemUrl($item) {
        $pageId = $item['link'];
        $cmsPage = $this->pageFactory->create()->load($pageId);
        return $this->getUrl($cmsPage->getIdentifier());
    }
    
    /**
     * Sort multiple dimensions array
     * 
     * @param array $array
     * @param string $column
     * @param string $direction
     * @return array
     */
    protected function sortArray($array, $column, $direction = SORT_ASC) {
        $sortColArr = array();
        if (!is_array($array)) return null;
        foreach ($array as $key => $row) {
            $sortColArr[$key] = $row[$column];
        }
        array_multisort($sortColArr, $direction, $array);
        return $array;
    }
        
}
