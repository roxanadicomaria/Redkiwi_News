<?php
namespace Redkiwi\News\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Redkiwi\News\Model\ImageUploader;
 
class Image extends AbstractHelper {
    
    const CACHE_DIRECTORY = 'cache';
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;
    
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;
        
    /**
     * Base path
     *
     * @var string
     */
    protected $basePath;
    
    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $_imageFactory;
        
    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $_ioObject;
    
    /**
     * Media directory object (writable).
     *
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;
    
    /**
     * @var ImageUploader
     */
    protected $imageUploader;
 
    /**
     * @param Context $context
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param \Magento\Framework\Filesystem\Io\File $io
     */
    public function __construct(
            Context $context,
            \Magento\Framework\Filesystem $filesystem,
            \Magento\Framework\UrlInterface $urlBuilder,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Framework\Image\AdapterFactory $imageFactory,
            \Magento\Framework\Filesystem\Io\File $io,
            ImageUploader $imageUploader
    ) {
        parent::__construct($context);
        $this->_filesystem = $filesystem;
        $this->_urlBuilder = $urlBuilder;
        $this->_storeManager = $storeManager;
        $this->_imageFactory = $imageFactory;
        $this->_ioObject = $io;        
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->imageUploader = $imageUploader;
        $this->basePath = $this->imageUploader->getBasePath();
    }
    
    /**
     * Get images base url
     *
     * @return string
     */
    public function getBaseUrl() {
        return $this->_storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ) . $this->basePath;
    }
    
    /**
     * Get base images directory
     *
     * @return string
     */
    public function getBaseDir() {
        return $this->mediaDirectory->getAbsolutePath($this->basePath);
    }
    
    /**
     * Remove image file
     * 
     * @param string $imageName
     * @return boolean
     */
    public function removeImage($imageName) { 
        $basePath = $this->basePath;        
        $baseImagePath = $this->getFilePath($basePath, $imageName);
        $this->mediaDirectory->delete($baseImagePath);
    }
    
    /**
     * Return URL for resized image
     * 
     * @param string $imageFile
     * @param integer $width
     * @param integer $height
     * @return string|boolean
     */
    public function resize($imageFile, $width, $height = '') {
        if (!$imageFile) {
            return false;
        }
        // prepare resize dimmensions
        $width = (int)$width;
        $height = (int)$height;
        if (!$height) {
            $height = $width;
        }
        // get image, and prepare paths
        $cacheDir = $this->getBaseDir().'/'.self::CACHE_DIRECTORY.'/'.$width.'/'.$height;
        $cacheUrl = $this->getBaseUrl().'/'.self::CACHE_DIRECTORY.'/'.$width.'/'.$height.'/';
        // if resized image exists return it
        if ($this->_ioObject->fileExists($cacheDir . $imageFile)) {
            return $cacheUrl . $imageFile;
        }
        try {
            $imagePath = $this->getBaseDir() . $imageFile;
            $resizedPath = $cacheDir . $imageFile;
            $imageResize = $this->_imageFactory->create();
            $imageResize->open($imagePath);
            $imageResize->constrainOnly(TRUE);
            $imageResize->keepTransparency(TRUE);
            $imageResize->keepFrame(FALSE);
            $imageResize->keepAspectRatio(true);
            $imageResize->resize($width, $height);
            $imageResize->save($resizedPath);
            return $cacheUrl . $imageFile;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Removes folder with cached images
     * 
     * @return boolean
     */
    public function flushImagesCache() {
        $cacheDir = $this->getBaseDir().'/'.self::CACHE_DIRECTORY.'/';
        if ($this->_ioObject->fileExists($cacheDir, false)) {
            return $this->_ioObject->rmdir($cacheDir, true);
        }
        return true;
    }
    
}
