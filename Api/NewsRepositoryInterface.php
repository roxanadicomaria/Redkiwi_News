<?php
namespace Redkiwi\News\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Redkiwi\News\Api\Data\NewsInterface;

interface NewsRepositoryInterface {
    
    /**
     * Save News
     *
     * @param NewsInterface $news
     * @return NewsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(NewsInterface $news);

    /**
     * Retrieve News by ID
     *
     * @param int $id
     * @return NewsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve News matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Redkiwi\News\Api\Data\NewsSearchResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete News
     *
     * @param NewsInterface $news
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(NewsInterface $news);

    /**
     * Delete News by ID
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
    
}
