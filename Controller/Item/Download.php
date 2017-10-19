<?php
namespace Redkiwi\News\Controller\Item;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Session\SessionManagerInterface;
use Redkiwi\News\Model\FileUploader;
 
class Download extends Action {
    
    /**
     * @var FileUploader
     */
    protected $fileUploader;
    
    /**
     * @var SessionManagerInterface
     */
    protected $session;
    
    /**
     * @param Context $context
     * @param FileUploader $fileUploader
     */
    public function __construct(
        Context $context,
        SessionManagerInterface $session,
        FileUploader $fileUploader
    ) {
        parent::__construct($context);
        $this->fileUploader = $fileUploader;
        $this->session = $session;
    }
 
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute() {
        // get file parameter from URL
        $encodedFile = $this->getRequest()->getParam('file');
        // decode parameter to get the file name
        $file = base64_decode($encodedFile);
        // get absolute file path
        $filepath = $this->fileUploader->getFileAbsoultePath($file);
        // prepare name, type and size
        $fileName = basename($filepath);
        $contentType = mime_content_type($filepath);
        $fileSize = filesize($filepath);
        // prepare response
        $this->getResponse()
                ->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                ->setHeader('Content-type', $contentType, true)
                ->setHeader('Content-Length', $fileSize)
                ->setHeader('Content-Disposition', 'attachment' . '; filename=' . $fileName);
        // send response
        $this->getResponse()->clearBody();
        $this->getResponse()->sendHeaders();
        // access file
        $handle = $this->fileUploader->getFile($file);
        // close write to session
        $this->session->writeClose();
        // read file in buffer and output the result
        while (true == ($buffer = $handle->read(1024))) {
            echo $buffer;
        }
    }
    
}
