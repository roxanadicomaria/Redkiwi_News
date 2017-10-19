<?php
namespace Redkiwi\News\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface NewsSearchResultInterface extends SearchResultsInterface {
    
    /**
     * Get News list.
     *
     * @return \Redkiwi\News\Api\Data\NewsInterface[]
     */
    public function getItems();

    /**
     * Set News list
     *
     * @param \Redkiwi\News\Api\Data\NewsInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
    
}
