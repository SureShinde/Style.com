<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvancedBookingSystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Question;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Review entity table
     *
     * @var string
     */
    private $_answerTable = null;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Answers Collection
     *
     * @var \Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Answer\CollectionFactory
     */
    protected $answerCollection;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface    $entityFactory
     * @param \Psr\Log\LoggerInterface                                     $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface                    $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface                   $storeManager
     * @param  \Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Answer\CollectionFactory $answerCollection
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null          $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null    $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Answer\CollectionFactory $answerCollection,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->_storeManager = $storeManager;
        $this->answerCollection = $answerCollection;
    }

    /**
     * Define resource model.
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\MpAdvancedBookingSystem\Model\Question::class,
            \Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Question::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
        $this->_map['fields']['status'] = 'main_table.status';
        $this->_map['fields']['customer_id'] = 'main_table.customer_id';
        $this->_map['fields']['nick_name'] = 'main_table.nick_name';
        $this->_map['fields']['created_at'] = 'main_table.created_at';
        $this->_map['fields']['updated_at'] = 'main_table.updated_at';
    }

    /**
     * Add filter by store.
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool                                 $withAdmin
     *
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }

        return $this;
    }

    /**
     * Add entity filter
     *
     * @return $this
     */
    public function addEntityFilter($entityId)
    {
        $answerTable = $this->getAnswerTable();
        $this->getSelect()->where('main_table.product_id = ?', $entityId);
        $ansColl = $this->answerCollection->create()->addFieldToFilter(
            'question_id',
            ['in' => $this->getAllIds()]
        );
        if ($ansColl->getSize()) {
            $this->getSelect()->joinLeft(
                $answerTable,
                'main_table.entity_id=' . $answerTable . '.question_id',
                [
                    'replies'=>"count(wk_mp_hotelbooking_answer.question_id)"
                ]
            )->group('main_table.entity_id');
        } else {
            $this->getSelect()->columns(['replies' => 0]);
        }

        return $this;
    }

    /**
     * Get review entity table name
     *
     * @return string
     */
    private function getAnswerTable()
    {
        if ($this->_answerTable === null) {
            $this->_answerTable = $this->getTable('wk_mp_hotelbooking_answer');
        }
        return $this->_answerTable;
    }
}
