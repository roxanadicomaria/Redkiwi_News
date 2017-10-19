<?php
namespace Redkiwi\News\Controller\Adminhtml\News;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Redkiwi\News\Api\Data\NewsInterfaceFactory;
use Redkiwi\News\Api\NewsRepositoryInterface;
use Redkiwi\News\Model\ImageUploader;
use Redkiwi\News\Model\FileUploader;

class Save extends \Magento\Backend\App\Action {
           
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Redkiwi_News::save_item';
    
    /**
     * News repository
     * 
     * @var NewsRepositoryInterface
     */
    protected $newsRepository;
    
    /**
     * News factory
     * 
     * @var NewsInterfaceFactory
     */
    protected $newsFactory;
    
    /**
     * @var PostDataProcessor
     */
    protected $dataProcessor;
    
    /**
     * @var ImageUploader
     */
    protected $imageUploader;
    
    /**
     * @var FileUploader
     */
    protected $fileUploader;


    /**
     * @param Context $context
     * @param NewsRepositoryInterface $newsRepository
     * @param NewsInterfaceFactory $newsFactory
     * @param PostDataProcessor $dataProcessor
     * @param ImageUploader $imageUploader
     * @param FileUploader $fileUploader
     */
    public function __construct(
        Context $context,
        NewsRepositoryInterface $newsRepository,
        NewsInterfaceFactory $newsFactory,
        PostDataProcessor $dataProcessor,
        ImageUploader $imageUploader,
        FileUploader $fileUploader
    ) {
        $this->newsRepository = $newsRepository;
        $this->newsFactory = $newsFactory;
        $this->dataProcessor = $dataProcessor;
        $this->imageUploader = $imageUploader;
        $this->fileUploader = $fileUploader;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute() {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $data = $this->dataProcessor->filter($data);
            $id = $this->getRequest()->getParam('id');
            $oldImage = '';
            $oldFile = '';
            if (empty($data['id'])) {
                unset($data['id']);
            }
            /** @var \Redkiwi\News\Model\News $model */
            if ($id) {
                $model = $this->newsRepository->getById((int)$id);  
                if (!$model->getId() && $id) {
                    $this->messageManager->addError(__('This news no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
                $oldImage = $model->getData('image');
                $oldFile = $model->getData('file');
            } else {
                $model = $this->newsFactory->create();
            }   
            // process image and file
            $data = $this->filePreprocessing($data);
            $data = $this->_filterFilePostData($data);
            // check and fill URL key
            $data = $this->_checkUrlKey($data);
             /*
             * prepare gallery data
             */
            // check if model have gallery already saved
            $savedGalleryData = [];
            if ($model->getGallery()) {
                $savedGalleryData = unserialize($model->getGallery());
            }
            //serialize data to be saved
            if (isset($data['gallery'])) { 
                $galleryData = $this->_processGalleryData($data['gallery'], $savedGalleryData);
                if ($galleryData) {
                    $data['gallery'] = serialize($galleryData);
                }
            }         
            // attach data to model
            $model->setData($data); 
            // prepare categories data
            if (isset($data['categories'])
                && is_array($data['categories'])
            ) {
                $model->setPostedCategories($data['categories']);
            }
            if (!$this->dataProcessor->validate($data)) {
                return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
            }            
            try {
                $model->save();
                // remove old image or current image
                if (array_key_exists('image', $data) && $data['image'] == null && $oldImage) {
                    $this->imageUploader->deleteImage($oldImage);
                }                
                $image = $model->getData('image');
                if ($image !== null) {
                    // remove old image
                    if ($oldImage && $oldImage != $image) {
                        $this->imageUploader->deleteImage($oldImage);
                    }
                } 
                
                // remove old file or current file
                if (array_key_exists('file', $data) && $data['file'] == null && $oldFile) {
                    $this->fileUploader->deleteFile($oldFile);
                }                
                $file = $model->getData('file');
                if ($file !== null) {
                    // remove old file
                    if ($oldFile && $oldFile != $file) {
                        $this->fileUploader->deleteFile($oldFile);
                    }
                } 

                $this->messageManager->addSuccess(__('You saved the news.'));
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->_getSession()->setNewsData($data);
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_getSession()->setNewsData($data);
                $this->messageManager->addException($e, __('Something went wrong while saving the news.'));
            }
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
    
    /**
     * Check if URL key is empty, and if so convert title to URL key
     * 
     * @param array $data
     * @return array
     */
    protected function _checkUrlKey($data) {        
        // check if URL key is empty and convert title as URL key
        if (empty($data[\Redkiwi\News\Api\Data\NewsInterface::URL_KEY])) {
            $title = $data[\Redkiwi\News\Api\Data\NewsInterface::TITLE];
            $data[\Redkiwi\News\Api\Data\NewsInterface::URL_KEY] = strtolower(urlencode(str_replace([' '], ['-'], $title)));
        }
        return $data;
    }

    /**
     * Files data preprocessing
     *
     * @param array $data
     *
     * @return array
     */
    public function filePreprocessing($data) {
        if (empty($data['image'])) {
            unset($data['image']);
            $data['image']['delete'] = true;
        }
        if (empty($data['file'])) {
            unset($data['file']);
            $data['file']['delete'] = true;
        }
        return $data;
    }
    
    /**
     * Filter files data
     *
     * @param array $rawData
     * @return array
     */
    protected function _filterFilePostData(array $rawData) {
        $data = $rawData;
        // @todo It is a workaround to prevent saving this data in image model and it has to be refactored in future
        if (isset($data['image']) && is_array($data['image'])) {
            if (!empty($data['image']['delete'])) {
                $data['image'] = null;
            } else {
                if (isset($data['image'][0]['name']) && isset($data['image'][0]['tmp_name'])) {
                    $data['image'] = $data['image'][0]['name'];
                } else {
                    unset($data['image']);
                }
            }
        }  
        if (isset($data['file']) && is_array($data['file'])) {
            if (!empty($data['file']['delete'])) {
                $data['file'] = null;
            } else {
                if (isset($data['file'][0]['name']) && isset($data['file'][0]['tmp_name'])) {
                    $data['file'] = $data['file'][0]['name'];
                } else {
                    unset($data['file']);
                }
            }
        }
        return $data;
    }
    /**
     * Process gallery data
     * 
     * @param array $newData
     * @param array $oldData
     * @return array
     */
    protected function _processGalleryData($newData, $oldData) {        
        $galleryData = [];
        if (empty($newData['value'])) {
            return false; // no data available, or gallery tab not opened
        }
        // prepare gallery data
        foreach ($newData['value'] as $key => $galleryItem) {
            // check if item was not deleted
            if (!$newData['delete'][$key]) {
                $galleryData[$key] = $galleryItem;
                // set sorting order
                $galleryData[$key]['sort_order'] = $newData['order'][$key];
                // set id
                $galleryData[$key]['id'] = $key;
                if (!empty($galleryItem['visible'])) {
                    $galleryData[$key]['checked'] = 'checked="checked"';
                }
                // process image
                try {                            
                    if (isset($oldData[$key]['image'])) {
                        // take previous image value
                        $galleryData[$key]['image'] = $oldData[$key]['image'];
                    }
                    // upload new image
                    try {
                        $imageFile = $this->imageUploader->saveFileToDir('gallery_'.$key.'_image');                        
                    } catch (\Exception $e) {
                        // nothing - file not uploaded
                    }
                    if (!empty($imageFile)) {
                        if (isset($oldData[$key]['image'])) {
                            $this->imageUploader->deleteImage($oldData[$key]['image']);
                        }
                        $galleryData[$key]['image'] = $imageFile['name'];
                    }
                } catch (\Exception $e) {   
                    $this->messageManager->addError($e->getMessage());
                }
            } else {
                // item was deleted
                if (isset($oldData[$key]['image'])) {
                    // remove image from server 
                    $this->imageUploader->deleteImage($oldData[$key]['image']);
                }
            }
        }
        return $galleryData;
    }
    

    
}
