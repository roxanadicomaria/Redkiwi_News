<?php
namespace Redkiwi\News\Model\Config\Source;
 
class Share implements \Magento\Framework\Option\ArrayInterface {
    
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        return [
            ['value' => 'facebook', 'label' => __('Facebook')], 
            ['value' => 'twitter', 'label' => __('Twitter')],
            ['value' => 'google', 'label' => __('Google+')],
            ['value' => 'pinterest', 'label' => __('Pinterest')]
        ];
    }
 
    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray() {
        $options = $this->toOptionArray();
        $array = [];
        foreach ($options as $option) {
            $array[$option['value']] = $option['label'];
        }
        return $array;
    }    
 
}
