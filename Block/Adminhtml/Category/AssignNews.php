<?php
namespace Redkiwi\News\Block\Adminhtml\Category;

class AssignNews extends \Magento\Backend\Block\Template {
    
    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'category/edit/assign_news.phtml';

    /**
     * @var \Redkiwi\News\Block\Adminhtml\Category\Tab\News
     */
    protected $blockGrid;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * AssignProducts constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->jsonEncoder = $jsonEncoder;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve instance of grid block
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBlockGrid() {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                'Redkiwi\News\Block\Adminhtml\Category\Tab\News',
                'category.news.grid'
            );
        }
        return $this->blockGrid;
    }

    /**
     * Return HTML of grid block
     *
     * @return string
     */
    public function getGridHtml() {
        return $this->getBlockGrid()->toHtml();
    }

    /**
     * @return string
     */
    public function getNewsJson() {
        $news = $this->getCategory()->getNewsPosition();
        if (!empty($news)) {
            return $this->jsonEncoder->encode($news);
        }
        return '{}';
    }
    /**
     * Retrieve current category instance
     *
     * @return array|null
     */
    public function getCategory() {
        return $this->registry->registry('category');
    }
    
}
