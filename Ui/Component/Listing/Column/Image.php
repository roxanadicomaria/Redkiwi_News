<?php
namespace Redkiwi\News\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Redkiwi\News\Helper\Image as ImageHelper;

class Image extends Column {
    
    const NAME = 'image';

    const ALT_FIELD = 'title';
    
    // Magento\Framework\View\Asset\Repository
    protected $assetRepo;
        
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ImageHelper $imageHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        AssetRepository $assetRepo,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->imageHelper = $imageHelper;
        $this->urlBuilder = $urlBuilder;
        $this->assetRepo = $assetRepo;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {  
                $itemObject = new \Magento\Framework\DataObject($item);
                $imageHelper = $this->imageHelper;
                if ($itemObject->getImage()) {
                    $imageUrl = $imageHelper->getBaseUrl() . $itemObject->getImage();
                } else {
                    $imageUrl = $this->assetRepo->getUrl("Redkiwi_News::images/no-image.gif");
                }
                $item[$fieldName . '_src'] = $imageUrl;
                $item[$fieldName . '_alt'] = $this->getAlt($item);
                $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                    'redkiwi_news/news/edit',
                    ['id' => $itemObject->getId()]
                );
                $item[$fieldName . '_orig_src'] = $imageUrl;
            }
        }
        return $dataSource;
    }

    /**
     * @param array $row
     *
     * @return null|string
     */
    protected function getAlt($row) {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;
        return isset($row[$altField]) ? $row[$altField] : null;
    }
    
}
