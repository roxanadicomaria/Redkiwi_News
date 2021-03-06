<?php
namespace Redkiwi\News\Controller\Adminhtml\News;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Redkiwi\News\Model\ResourceModel\News\CollectionFactory;
use Redkiwi\News\Api\NewsRepositoryInterface;

class MassEnable extends Action {
   
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Redkiwi_News::news';
    
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    
    /**
     * News repository
     * 
     * @var NewsRepositoryInterface
     */
    protected $newsRepository;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param NewsRepositoryInterface $newsRepository
     */
    public function __construct(
            Context $context, 
            Filter $filter, 
            CollectionFactory $collectionFactory,
            NewsRepositoryInterface $newsRepository
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->newsRepository = $newsRepository;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute() {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
        foreach ($collection as $item) {
            $item->setStatus(\Redkiwi\News\Model\News::STATUS_ENABLED);
            $this->newsRepository->save($item);
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) were enabled.', $collectionSize));
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
    
}
