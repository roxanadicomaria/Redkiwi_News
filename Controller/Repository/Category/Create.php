<?php
namespace Redkiwi\News\Controller\Repository\Category;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Redkiwi\News\Api\CategoryRepositoryInterface;
use Redkiwi\News\Api\Data\CategoryInterfaceFactory;

class Create extends Action {
    
    /**
     * @var CategoryRepositoryInterface 
     */
    private $categoryRepository;
    
    /**
     * @var CategoryInterface
     */
    protected $categoryInterfaceFactory;
    
    /**
     * 
     * @param Context $context
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CategoryInterfaceFactory $categoryInterfaceFactory
     */
    public function __construct(
        Context $context,
        CategoryRepositoryInterface $categoryRepository,
        CategoryInterfaceFactory $categoryInterfaceFactory
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->categoryInterfaceFactory = $categoryInterfaceFactory;
        parent::__construct($context);
    }

    /**
     * Show a list with active news
     */
    public function execute() {
        $this->getResponse()->setHeader('content-type', 'text/plain');
        
        // prepare a category object
        $category = $this->categoryInterfaceFactory->create();
        $category->setContent('<p>Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.</p>')
                ->setTitle('Charity')
                ->setUrlKey('charity')
                ->setPosition(50)
                ->setMetaDescription('News about our charity campaigns')
                ->setMetaKeywords('Charity, News');
        // save category
        $categoryRepository = $this->categoryRepository->save($category);
        $this->getResponse()->appendBody(sprintf(
                "%s (%d)\n",
                $categoryRepository->getTitle(),
                $categoryRepository->getId()
        ));
    }
    
}
