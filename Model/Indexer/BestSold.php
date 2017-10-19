<?php
namespace Redkiwi\News\Model\Indexer;

use Magento\Framework\Indexer\IndexerRegistry;
use Magento\Framework\Indexer\CacheContext;
use Redkiwi\News\Model\Indexer\Order\Item\Action\Row as RowIndexer;
use Redkiwi\News\Model\Indexer\Order\Item\Action\Rows as RowsIndexer;
use Redkiwi\News\Model\Indexer\Order\Item\Action\Full as FullIndexer;

class BestSold implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface {
    
    /**
     * Best sold cache tag
     */
    const CACHE_TAG = 'best_sold_products';
    
    /**
     * Index table name
     */
    const INDEX_TABLE = \Redkiwi\News\Setup\InstallSchema::NEWS_BEST_SOLD_PRODUCTS_TABLE;
    
    /**
     * Sales order item table name
     */
    const SALES_ORDER_ITEM_TABLE = 'sales_order_item';

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    protected $indexerRegistry;
    
    /**
     * @var RowIndexer
     */
    protected $_bestSoldIndexerRow;

    /**
     * @var RowsIndexer
     */
    protected $_bestSoldIndexerRows;

    /**
     * @var FullIndexer
     */
    protected $_bestSoldIndexerFull;
    
    /**
     * @var \Magento\Framework\Indexer\CacheContext
     */
    private $cacheContext;

    /**
     * @param IndexerRegistry $indexerRegistry
     * @param RowIndexer $bestSoldIndexerRow
     * @param RowsIndexer $bestSoldIndexerRows
     * @param FullIndexer $bestSoldIndexerFull
     */
    public function __construct(
        IndexerRegistry $indexerRegistry,
        RowIndexer $bestSoldIndexerRow,
        RowsIndexer $bestSoldIndexerRows,
        FullIndexer $bestSoldIndexerFull
    ) {
        $this->indexerRegistry = $indexerRegistry;
        $this->_bestSoldIndexerRow = $bestSoldIndexerRow;
        $this->_bestSoldIndexerRows = $bestSoldIndexerRows;
        $this->_bestSoldIndexerFull = $bestSoldIndexerFull;
    }

    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     */
    public function execute($ids = null) {
        $this->_bestSoldIndexerFull->execute($ids);
        $this->getCacheContext()->registerEntities(self::CACHE_TAG, $ids);
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull() {
        $this->_bestSoldIndexerFull->execute();
        $this->getCacheContext()->registerTags(
            [
                self::CACHE_TAG
            ]
        );        
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids) {
        $this->_bestSoldIndexerRows->execute($ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id) {
        $this->_bestSoldIndexerRow->execute($id);
    }
    
    /**
     * Get cache context
     *
     * @return \Magento\Framework\Indexer\CacheContext
     * @deprecated
     */
    protected function getCacheContext()
    {
        if (!($this->cacheContext instanceof CacheContext)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(CacheContext::class);
        } else {
            return $this->cacheContext;
        }
    }
    
}
