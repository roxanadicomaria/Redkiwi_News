<?php
namespace Redkiwi\News\Model\Indexer\Order\Item\Action;

class Full extends \Redkiwi\News\Model\Indexer\Order\Item\AbstractAction {
    
    /**
     * Execute Full reindex
     *
     * @param array|int|null $ids
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute($ids = null) {        
        try {
            $this->_reindex($ids);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }
    
}
