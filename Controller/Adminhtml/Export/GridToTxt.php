<?php
namespace Redkiwi\News\Controller\Adminhtml\Export;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Redkiwi\News\Model\Export\ConvertToTxt;

class GridToTxt extends Action {
    
    /**
     * @var ConvertToTxt
     */
    protected $converter;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @param Context $context
     * @param ConvertToTxt $converter
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Context $context,
        ConvertToTxt $converter,
        FileFactory $fileFactory
    ) {
        parent::__construct($context);
        $this->converter = $converter;
        $this->fileFactory = $fileFactory;
    }

    /**
     * Export data provider to TXT
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute() {
        return $this->fileFactory->create('export.txt', $this->converter->getTxtFile(), 'var');
    }
    
}
