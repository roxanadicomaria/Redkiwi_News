<?php
namespace Redkiwi\News\Controller\Adminhtml\News;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Redkiwi\News\Api\NewsRepositoryInterface;
use Redkiwi\News\Api\Data\NewsInterface;

class InlineEdit extends \Magento\Backend\App\Action {
     
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Redkiwi_News::save_item';
  
    /** @var NewsRepositoryInterface  */
    protected $newsRepository;

    /** @var JsonFactory  */
    protected $jsonFactory;

    /**
     * @param Context $context
     * @param NewsRepositoryInterface $newsRepository
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        NewsRepositoryInterface $newsRepository,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->newsRepository = $newsRepository;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute() {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];
        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (!count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($postItems) as $itemId) {
                    /** @var \Redkiwi\News\Model\News $item */
                    $item = $this->newsRepository->getById($itemId);
                    try {
                        $item->setData(array_merge($item->getData(), $postItems[$itemId]));
                        $this->newsRepository->save($item);
                    } catch (\Exception $e) {
                        $messages[] = $this->getErrorWithItemId(
                            $item,
                            __($e->getMessage())
                        );
                        $error = true;
                    }
                }
            }
        }
        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add news ID to error message
     *
     * @param NewsInterface $item
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithItemId(NewsInterface $item, $errorText){
        return '[News ID: ' . $item->getId() . '] ' . $errorText;
    }
    
}
