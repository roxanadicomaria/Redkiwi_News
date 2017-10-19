<?php
namespace Redkiwi\News\Api\Data;

interface CategoryInterface {
    
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const CATEGORY_ID               = 'id';
    const URL_KEY                   = 'url_key';
    const TITLE                     = 'title';
    const CONTENT                   = 'content';
    const CREATED_AT                = 'created_at';
    const UPDATED_AT                = 'updated_at';
    const STORES                    = 'stores';
    const STATUS                    = 'status';
    const POSITION                  = 'position';
    const META_DESCRIPTION          = 'meta_description';
    const META_KEYWORDS             = 'meta_keywords';
    /**#@-*/
    
    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();
    
    /**
     * Set ID
     *
     * @param int $id
     * @return CategoryInterface
     */
    public function setId($id);

    /**
     * Get Url Key
     *
     * @return string
     */
    public function getUrlKey();

    /**
     * Set Url Key
     *
     * @param string $url_key
     * @return CategoryInterface
     */
    public function setUrlKey($url_key);
    
    /**
     * Get title
     *
     * @return string
     */
    public function getTitle();
    
    /**
     * Set title
     *
     * @param string $title
     * @return CategoryInterface
     */
    public function setTitle($title);

    /**
     * Get content
     *     
     * @return string
     */
    public function getContent();
    
    /**
     * Set content
     *
     * @param string $content
     * @return CategoryInterface
     */
    public function setContent($content);
    
    /**
     * Get creation date
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set creation date
     *
     * @param string $createdAt
     * @return CategoryInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get update date
     *
     * @return string|null
     */
    public function getUpdatedAt();
    
    /**
     * Set update date
     *
     * @param string $updateAt
     * @return CategoryInterface
     */
    public function setUpdatedAt($updateAt);
    
    /**
     * Get stores
     *
     * @return string
     */
    public function getStores();
    
    /**
     * Set stores
     *
     * @param string $stores
     * @return CategoryInterface
     */
    public function setStores($stores);
    
    /**
     * Get status
     *
     * @return int
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param int $status
     * @return CategoryInterface
     */
    public function setStatus($status);
    
    /**
     * Get position
     *
     * @return string
     */
    public function getPosition();

    /**
     * Set position
     *
     * @param string $position
     * @return CategoryInterface
     */
    public function setPosition($position);
    
    /**
     * Get meta description
     *
     * @return string
     */
    public function getMetaDescription();

    /**
     * Set meta description
     *
     * @param string $metaDescription
     * @return CategoryInterface
     */
    public function setMetaDescription($metaDescription);
    
    /**
     * Get meta keywords
     *
     * @return string
     */
    public function getMetaKeywords();

    /**
     * Set meta keywords
     *
     * @param string $metaKeywords
     * @return CategoryInterface
     */
    public function setMetaKeywords($metaKeywords);
    
}
