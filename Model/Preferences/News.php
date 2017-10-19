<?php
namespace Redkiwi\News\Model\Preferences;

class News extends \Redkiwi\News\Model\News {
        
    /**
     * Get content
     *     
     * @return string
     */
    public function getContent() {
        return '<hr/>' . $this->getData(self::CONTENT) . '<hr/>';
    }  
    
}
