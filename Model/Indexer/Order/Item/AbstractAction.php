<?php
namespace Redkiwi\News\Model\Indexer\Order\Item;

use Magento\Framework\App\ResourceConnection;

abstract class AbstractAction {
    
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;
    
    /**
     * @param ResourceConnection $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Config $config
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Config $config
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    /**
     * Execute action for given ids
     *
     * @param array|int $ids
     * @return void
     */
    abstract public function execute($ids);

    /**
     * Removes data from table
     *
     * @param string $table
     * @return void
     */
    protected function _emptyTable($table, $changedIds = []) {                
        if (empty($changedIds)) {
            // no IDs provided (full reindex) > truncate entire table
            $this->connection->truncateTable($table);
        } else {
            // prepare query to select indexed products
            $select = $this->connection->select()->from(
                $table,
                null
            )->where(
                'product_id IN(?)', $changedIds
            );
            // transform select query in delete
            $sql = $select->deleteFromSelect($table);            
            // run query
            $this->connection->query($sql);            
        }
    }
    
    /**
     * Get indexer table name
     * 
     * @return string
     */
    protected function _getIdxTable() {
        // prepare index table name
        return $this->connection->getTableName(\Redkiwi\News\Model\Indexer\BestSold::INDEX_TABLE);
    }

    /**
     * Refresh entities index
     *
     * @param array $changedIds
     * @return array Affected ids
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _reindex($changedIds = []) {        
        $indexTable = $this->_getIdxTable();
        // empty reindex table of products with changedIds        
        $this->_emptyTable($indexTable, $changedIds);
                
        // prepare query to select all sold items
        $salesItemTable = $this->connection->getTableName(\Redkiwi\News\Model\Indexer\BestSold::SALES_ORDER_ITEM_TABLE);
        $select = $this->connection->select()->from(
            $salesItemTable,
            ['order_id', 'parent_item_id', 'store_id', 'product_id', 'qty_ordered', 'qty_refunded', 'qty_canceled']
        );
        if (!empty($changedIds)) {
            $select->where(
                'product_id IN(?)', $changedIds
            );
        }      
        // run query
        $items = $this->connection->fetchAll($select);
        // prepare data to reindex
        $dataToIndex = [];
        foreach ($items as $item) {
            $qty = $item['qty_ordered'] - $item['qty_refunded'] - $item['qty_canceled'];
            $key = $item['product_id'] . '-' . $item['store_id'];
            if (isset($dataToIndex[$key])) {
                $dataToIndex[$key]['qty_sold'] += $qty;
            } else {
                $dataToIndex[$key] = [
                    'product_id'    => $item['product_id'],
                    'store_id'      => $item['store_id'],
                    'qty_sold'      => $qty,
                    'updated_at'    => date('Y-m-d H:i:s')
                ];
            }
        }
        // insert reindex data in table
        $this->connection->insertMultiple($indexTable, $dataToIndex);        
        return $changedIds;
    }
        
}
