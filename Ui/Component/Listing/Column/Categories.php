<?php
namespace Redkiwi\News\Ui\Component\Listing\Column;

use Magento\Framework\Escaper;
use Magento\Framework\Data\OptionSourceInterface;
use Redkiwi\News\Model\CategoryFactory;

class Categories implements OptionSourceInterface {
    
    /**
     * Escaper
     * @var Escaper
     */
    protected $escaper;

    /**
     * Categories
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var array
     */
    protected $currentOptions = [];

    /**
     * Constructor
     *
     * @param CategoryFactory $categoryFactory
     * @param Escaper $escaper
     */
    public function __construct(CategoryFactory $categoryFactory, Escaper $escaper) {
        $this->categoryFactory = $categoryFactory;
        $this->escaper = $escaper;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray() {
        if ($this->options !== null) {
            return $this->options;
        }        
        $this->generateCurrentOptions();
        $this->options = array_values($this->currentOptions);
        return $this->options;
    }
    
    /**
     * Generate current options
     *
     * @return void
     */
    protected function generateCurrentOptions() {
        $this->currentOptions['No Category']['label'] = __('-- No category --');
        $this->currentOptions['No Category']['value'] = 0;
        $collection = $this->categoryFactory->create()
                ->getCollection()
                ->setOrder('title','ASC');
        /** @var \Redkiwi\News\Model\Category $item */
        foreach ($collection as $item) {
            $name = $this->escaper->escapeHtml($item->getTitle());
            $this->currentOptions[$name]['label'] = $name;
            $this->currentOptions[$name]['value'] = $item->getId();
        }
    }
    
}
