<?php
namespace Redkiwi\News\Observer;

use Magento\Framework\Event\ObserverInterface;
use Redkiwi\News\Model\Indexer\Order\Item\Processor;

class OrderItemSave implements ObserverInterface {
    
    /**
     * @var Processor
     */
    protected $indexProcessor;

    /**
     * @param Processor $indexProcessor
     */
    public function __construct(
        Processor $indexProcessor
    ) {
        $this->indexProcessor = $indexProcessor;
    }
    
    /**
     * Observe order item save and run best sold indexer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $item = $observer->getEvent()->getItem();
        $this->indexProcessor->reindexRow($item->getProductId());
    }
    
}
