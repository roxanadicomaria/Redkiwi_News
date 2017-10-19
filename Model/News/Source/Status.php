<?php
namespace Redkiwi\News\Model\News\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface {
    
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray() {
        $options = [
            [
                'value' => \Redkiwi\News\Model\News::STATUS_ENABLED,
                'label' => __('Enabled')
            ],
            [
                'value' => \Redkiwi\News\Model\News::STATUS_DISABLED,
                'label' => __('Disabled')
            ]
        ];
        return $options;
    }
    
       /**
     * Get options array
     *
     * @return array
     */
    public function getOptionArray() {
        $options = [];
        foreach ($this->toOptionArray() as $option) {
            $options[$option['value']] = (string)$option['label'];
        }
        return $options;
    }   
    
}
