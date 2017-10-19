<?php
namespace Redkiwi\News\Model\Indexer\Order\Item\Action;

class Row extends \Redkiwi\News\Model\Indexer\Order\Item\AbstractAction {
    
    /**
     * Execute Row reindex
     *
     * @param int|null $id
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute($id = null) {
        if (!isset($id) || empty($id)) {
            throw new \Magento\Framework\Exception\InputException(
                __('We can\'t rebuild the index for an undefined order item.')
            );
        }
        try {
            $this->_reindex([$id]);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()), $e);
        }
    }
    
}
