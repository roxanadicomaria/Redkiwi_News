<?php
namespace Redkiwi\News\Controller\Repository;
 
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Redkiwi\News\Api\NewsRepositoryInterface;
use Redkiwi\News\Api\Data\NewsInterfaceFactory;
 
class Create extends Action {
    
    /**
     * @var NewsRepositoryInterface 
     */
    private $newsRepository;
    
    /**
     * @var NewsInterface
     */
    protected $newsInterfaceFactory;
    
    /**
     * 
     * @param Context $context
     * @param NewsRepositoryInterface $newsRepository
     * @param NewsInterfaceFactory $newsInterfaceFactory
     */
    public function __construct(
        Context $context,
        NewsRepositoryInterface $newsRepository,
        NewsInterfaceFactory $newsInterfaceFactory
    ) {
        $this->newsRepository = $newsRepository;
        $this->newsInterfaceFactory = $newsInterfaceFactory;
        parent::__construct($context);
    }
 
    /**
     * Show a list with active news
     */
    public function execute() {
        $this->getResponse()->setHeader('content-type', 'text/plain');
        
        // prepare a news object
        $news = $this->newsInterfaceFactory->create();
        $news->setAuthor('Hernandez')
                ->setContent('<p>Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus id quod maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.</p>')
                ->setShortContent('<p>Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur?</p>')
                ->setImage('https://static.pexels.com/photos/29017/pexels-photo-29017.jpg')
                ->setTitle('Harum')
                ->setUrlKey('harum')
                ->setMetaDescription('Quis autem vel eum iure reprehenderit qui in ea voluptate')
                ->setMetaKeywords('Harum, News');
        // save news 
        $newsRepository = $this->newsRepository->save($news);
        $this->getResponse()->appendBody(sprintf(
                "%s (%d)\n",
                $newsRepository->getTitle(),
                $newsRepository->getId()
        ));
    }
    
}
