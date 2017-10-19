<?php
namespace Redkiwi\News\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface CategorySearchResultInterface extends SearchResultsInterface {
    
    /**
     * Get Categories list.
     *
     * @return \Redkiwi\News\Api\Data\CategoryInterface[]
     */
    public function getItems();

    /**
     * Set Categories list
     *
     * @param \Redkiwi\News\Api\Data\CategoryInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
    
}
