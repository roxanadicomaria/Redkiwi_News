<?php
namespace Redkiwi\News\Controller\Adminhtml\Category;

use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\Message\ManagerInterface;

class PostDataProcessor {
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $dateFilter;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * 
     * @param Date $dateFilter
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Date $dateFilter,
        ManagerInterface $messageManager
    ) {
        $this->dateFilter = $dateFilter;
        $this->messageManager = $messageManager;
    }

    /**
     * Filtering posted data. Converting localized data if needed
     *
     * @param array $data
     * @return array
     */
    public function filter($data) {
        // convert stores array to string
        if (!empty($data['stores'])) {
            $data['stores'] = implode(',', $data['stores']);
        }
        $filterRules = [];
        // filter dates
        foreach (['created_at', 'updated_at'] as $dateField) {
            if (!empty($data[$dateField])) {
                $filterRules[$dateField] = $this->dateFilter;
            }
        }
        return (new \Zend_Filter_Input($filterRules, [], $data))->getUnescaped();
    }

    /**
     * Validate post data
     *
     * @param array $data
     * @return bool     Return FALSE if someone item is invalid
     */
    public function validate($data) {
        $requiredFields = [
            'title' => __('Title'),
            'stores' => __('Store View'),
            'status' => __('Status'),
            'url_key' => __('URL Key'),
            'content' => __('Content'),
            'position' => __('Position')
        ];
        $errorNo = true;
        foreach ($data as $field => $value) {
            if (in_array($field, array_keys($requiredFields)) && $value == '') {
                $errorNo = false;
                $this->messageManager->addError(
                    __('To apply changes you should fill in required "%1" field', $requiredFields[$field])
                );
            }
        }
        return $errorNo;
    }

}
