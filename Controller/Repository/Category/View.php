<?php
namespace Redkiwi\News\Controller\Repository\Category;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Redkiwi\News\Api\CategoryRepositoryInterface;

class View extends Action {
    
    /**
     * @var CategoryRepositoryInterface 
     */
    private $categoryRepository;
    
    /**
     * 
     * @param Context $context
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        Context $context,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->categoryRepository = $categoryRepository;
        parent::__construct($context);
    }

    /**
     * Show category details
     */
    public function execute() {
        $this->getResponse()->setHeader('content-type', 'text/plain');        
        // load category by ID
        $categoryItem = $this->categoryRepository->getById(2);           
        $this->getResponse()->appendBody(sprintf(
                "%s (%d)\n %s",
                $categoryItem->getTitle(),
                $categoryItem->getId(),
                $categoryItem->getContent()
        ));
    }
    
}
