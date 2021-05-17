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

namespace Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Question\FrontGrid;

use Magento\Framework\Api\Search\SearchResultInterface as ApiSearchResultInterface;
use Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Question\Collection as QuestionCollection;
use Magento\Framework\Search\AggregationInterface as SearchAggregationInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb as ResourceModelAbstractDb;

use Webkul\Marketplace\Helper\Data as HelperData;

/**
 * Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Question\FrontGrid\Collection Class
 * Collection for displaying grid of MpAdvancedBookingSystem seller Question.
 */
class Collection extends QuestionCollection implements ApiSearchResultInterface
{
    /**
     * @var SearchAggregationInterface
     */
    protected $aggregations;

    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * @param EntityFactoryInterface $entityFactoryInterface
     * @param LoggerInterface $loggerInterface
     * @param FetchStrategyInterface $fetchStrategyInterface
     * @param EventManagerInterface $eventManagerInterface
     * @param StoreManagerInterface $storeManagerInterface
     * @param HelperData $helperData
     * @param \Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Answer\CollectionFactory $answerCollection
     * @param \Webkul\MpAdvancedBookingSystem\Block\Hotelbooking\Questions $questionBlock
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param $mainTable
     * @param $eventPrefix
     * @param $eventObject
     * @param $resourceModel
     * @param string $model
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param ResourceModelAbstractDb $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactoryInterface,
        LoggerInterface $loggerInterface,
        FetchStrategyInterface $fetchStrategyInterface,
        EventManagerInterface $eventManagerInterface,
        StoreManagerInterface $storeManagerInterface,
        HelperData $helperData,
        \Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Answer\CollectionFactory $answerCollection,
        \Webkul\MpAdvancedBookingSystem\Block\Hotelbooking\Questions $questionBlock,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        ResourceModelAbstractDb $resource = null
    ) {
        $this->helperData = $helperData;
        parent::__construct(
            $entityFactoryInterface,
            $loggerInterface,
            $fetchStrategyInterface,
            $eventManagerInterface,
            $storeManagerInterface,
            $answerCollection,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->questionBlock = $questionBlock;
        $this->eavAttribute = $eavAttribute;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
        $this->_map['fields']['name'] = 'cpev.value';
    }

    /**
     * @return SearchAggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * @param SearchAggregationInterface $aggregationsData
     *
     * @return $this
     */
    public function setAggregations($aggregationsData)
    {
        $this->aggregations = $aggregationsData;
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return $this
     */
    public function setSearchCriteria(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null
    ) {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     *
     * @return $this
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     *
     * @return $this
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $quesColl = $this->questionBlock->getAllQuestions();
        $ids = [];
        $storeId = $this->helperData->getCurrentStoreId();
        if ($quesColl && $quesColl->getSize()) {
            $ids = $quesColl->getAllIds();
            $this->addFieldToFilter('entity_id', ['in' => $ids]);
            $catalogProductEntity = $this->getTable(
                'catalog_product_entity'
            );
            $catalogProductEntityVarchar = $this->getTable(
                'catalog_product_entity_varchar'
            );
            $proAttId = $this->eavAttribute->getIdByCode('catalog_product', 'name');

            $this->getSelect()->join(
                $catalogProductEntityVarchar.' as cpev',
                'main_table.product_id = cpev.entity_id',
                [
                    'main_product_id'=>"cpev.entity_id",
                    'name' => "cpev.value"
                ]
            )->where(
                'cpev.store_id = '.$storeId.' AND
                cpev.attribute_id = '.$proAttId
            );

            $this->getSelect()->join(
                $catalogProductEntity.' as cpe',
                'main_table.product_id = cpe.entity_id',
                [
                    'main_product_id'=>"cpe.entity_id"
                ]
            );
            $this->getSelect()->group('product_id');
        }

        $answerTable = $this->getTable('wk_mp_hotelbooking_answer');
        $ansColl = $this->answerCollection->create()->addFieldToFilter(
            'question_id',
            ['in' => $ids]
        );
        if ($ansColl->getSize()) {
            $this->getSelect()->joinLeft(
                $answerTable,
                'main_table.entity_id=' . $answerTable . '.question_id',
                [
                    'replies'=>"count(wk_mp_hotelbooking_answer.question_id)"
                ]
            );
        } else {
            $this->getSelect()->columns(['replies' => new \Zend_Db_Expr('0')]);
        }
        $this->getSelect()->group('main_table.entity_id');
        parent::_renderFiltersBefore();
    }
}
