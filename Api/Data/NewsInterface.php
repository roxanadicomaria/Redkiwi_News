<?php
namespace Redkiwi\News\Api\Data;

interface NewsInterface {
    
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const NEWS_ID                   = 'id';
    const URL_KEY                   = 'url_key';
    const TITLE                     = 'title';
    const CONTENT                   = 'content';
    const SHORT_CONTENT             = 'short_content';
    const PUBLISH_AT                = 'publish_at';
    const CREATED_AT                = 'created_at';
    const UPDATED_AT                = 'updated_at';
    const STORES                    = 'stores';
    const STATUS                    = 'status';
    const AUTHOR                    = 'author';
    const META_DESCRIPTION          = 'meta_description';
    const META_KEYWORDS             = 'meta_keywords';
    const IMAGE                     = 'image';
    const FILE                      = 'file';
    const GALLERY                   = 'gallery';
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
     * @return NewsInterface
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
     * @return NewsInterface
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
     * @return NewsInterface
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
     * @return NewsInterface
     */
    public function setContent($content);
    
    /**
     * Get short content
     *     
     * @return string
     */
    public function getShortContent();
    
    /**
     * Set short content
     *
     * @param string $shortContent
     * @return NewsInterface
     */
    public function setShortContent($shortContent);
    
    /**
     * Get publish date
     *
     * @return string|null
     */
    public function getPublishAt();

    /**
     * Set publish date
     *
     * @param string $publishAt
     * @return NewsInterface
     */
    public function setPublishAt($publishAt);
    
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
     * @return NewsInterface
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
     * @return NewsInterface
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
     * @return NewsInterface
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
     * @return NewsInterface
     */
    public function setStatus($status);
    
    /**
     * Get author
     *
     * @return string
     */
    public function getAuthor();

    /**
     * Set author
     *
     * @param string $author
     * @return NewsInterface
     */
    public function setAuthor($author);
    
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
     * @return NewsInterface
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
     * @return NewsInterface
     */
    public function setMetaKeywords($metaKeywords);
    
    /**
     * Get image
     *
     * @return string
     */
    public function getImage();

    /**
     * Set image
     *
     * @param string $image
     * @return NewsInterface
     */
    public function setImage($image);
    
     /**
     * Get file
     *
     * @return string
     */
    public function getFile();

    /**
     * Set file
     *
     * @param string $file
     * @return NewsInterface
     */
    public function setFile($file);
    
    /**
     * Get gallery
     *
     * @return string
     */
    public function getGallery();

    /**
     * Set gallery
     *
     * @param string $gallery
     * @return NewsInterface
     */
    public function setGallery($gallery);

}
