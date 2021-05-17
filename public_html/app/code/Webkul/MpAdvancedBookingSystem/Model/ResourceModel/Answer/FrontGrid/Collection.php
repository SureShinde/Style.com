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

namespace Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Answer\FrontGrid;

use Magento\Framework\Api\Search\SearchResultInterface as ApiSearchResultInterface;
use Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Answer\Collection as AnswerCollection;
use Magento\Framework\Search\AggregationInterface as SearchAggregationInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb as ResourceModelAbstractDb;
use Magento\Framework\App\RequestInterface;

/**
 * Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Answer\FrontGrid\Collection Class
 * Collection for displaying grid of MpAdvancedBookingSystem seller's question's answers.
 */
class Collection extends AnswerCollection implements ApiSearchResultInterface
{
    /**
     * @var SearchAggregationInterface
     */
    protected $aggregations;

    /**
     * @param EntityFactoryInterface $entityFactoryInterface
     * @param LoggerInterface $loggerInterface
     * @param FetchStrategyInterface $fetchStrategyInterface
     * @param EventManagerInterface $eventManagerInterface
     * @param StoreManagerInterface $storeManagerInterface
     * @param \Webkul\MpAdvancedBookingSystem\Block\Hotelbooking\Question\Answer $answerBlock
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
        \Webkul\MpAdvancedBookingSystem\Block\Hotelbooking\Question\Answer $answerBlock,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        RequestInterface $request,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        ResourceModelAbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactoryInterface,
            $loggerInterface,
            $fetchStrategyInterface,
            $eventManagerInterface,
            $storeManagerInterface,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
        $params = $request->getParams();
        if (!empty($params['question_id']) && !$answerBlock->isValidQuestionId()) {
            $messageManager->addError(
                __("Invalid Question")
            );
            return $redirect->redirect(
                $response,
                'mpadvancebooking/hotelbooking/questions'
            );
        }
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
   /*  protected function _renderFiltersBefore()
    {
        $params = $this->request->getParams();
        if (!empty($params['question_id'])) {
            $ansColl = $this->answerBlock->getAllAnswers();
            $ids = [];
            if ($ansColl && $ansColl->getSize()) {
                $ids = $ansColl->getAllIds();
            }
            $this->addFieldToFilter('entity_id',['in' => $ids]);
        }
        parent::_renderFiltersBefore();
    } */
}
