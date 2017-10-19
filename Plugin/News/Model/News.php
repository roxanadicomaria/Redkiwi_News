<?php
namespace Redkiwi\News\Plugin\News\Model;

class News {
    
    /**
     * Alter author value
     * 
     * @param \Redkiwi\News\Model\News $subject
     * @param string $result
     * @return string
     */
    public function afterGetAuthor(\Redkiwi\News\Model\News $subject, $result) {
        $result = '['.$result.']';
        return $result;
    }
    
}
