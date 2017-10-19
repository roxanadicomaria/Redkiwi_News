<?php
namespace Redkiwi\News\Controller\Repository;
 
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Redkiwi\News\Api\NewsRepositoryInterface;
 
class View extends Action {
    
    /**
     * @var NewsRepositoryInterface 
     */
    private $newsRepository;
    
    /**
     * 
     * @param Context $context
     * @param NewsRepositoryInterface $newsRepository
     */
    public function __construct(
        Context $context,
        NewsRepositoryInterface $newsRepository
    ) {
        $this->newsRepository = $newsRepository;
        parent::__construct($context);
    }
 
    /**
     * Show a list with active news
     */
    public function execute() {
        $this->getResponse()->setHeader('content-type', 'text/plain');        
        // load news item by ID
        $newsItem = $this->newsRepository->getById(3);           
        $this->getResponse()->appendBody(sprintf(
                "%s (%d)\n %s\n\n %s (%s)",
                $newsItem->getTitle(),
                $newsItem->getId(),
                $newsItem->getShortContent(),
                $newsItem->getAuthor(),
                $newsItem->getPublishAt()
        ));
    }
    
}
