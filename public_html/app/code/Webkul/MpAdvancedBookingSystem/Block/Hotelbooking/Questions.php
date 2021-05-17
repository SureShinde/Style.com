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

namespace Webkul\MpAdvancedBookingSystem\Block\Hotelbooking;

/**
 * Webkul MpAdvancedBookingSystem Questions Block
 */
class Questions extends \Webkul\MpAdvancedBookingSystem\Block\Hotelbooking
{
    /**
     * @var \Webkul\MpAdvancedBookingSystem\Model\Question
     */
    protected $productlists;

    /**
     * getAllQuestions
     *
     * @return bool|\Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Question\Collection
     */
    public function getAllQuestions()
    {
        $storeId = $this->mpHelper->getCurrentStoreId();
        $websiteId = $this->mpHelper->getWebsiteId();
        if (!($customerId = $this->mpHelper->getCustomerId())) {
            return false;
        }
        if (!$this->productlists) {
            $filterParams = $this->getFilterParams();
            $answerTable = $this->mpProductCollection->create()->getTable('wk_mp_hotelbooking_answer');

            /* Get Seller Product Collection for current Store Id */
            $storeProductIDs = $this->getSellerProductCollection($customerId, $filterParams['filter']);

            /* Get Seller Product Collection for 0 Store Id */
            $adminProductIDs = $this->getAdminProductCollection($customerId, $filterParams['filter']);

            $productIDs = array_merge($storeProductIDs, $adminProductIDs);

            $bookingProduct = $this->infoCollection->create();
            $allIds = $bookingProduct->getAllProductIds();
            $productIDs = array_unique(array_intersect($productIDs, $allIds));

            $collection = $this->questionCollection->create();
            if ($filterParams['filterStatus']!=='') {
                $collection->addFieldToFilter(
                    'status',
                    ['eq' => $filterParams['filterStatus']]
                );
            }
            if ($filterParams['from'] && $filterParams['to']) {
                $collection->addFieldToFilter(
                    'created_at',
                    [
                        'from' => $filterParams['from'],
                        'to' => $filterParams['to'],
                        'date' => true,
                    ]
                );
            }

            $ansColl = $this->answerCollection->create()
                ->addFieldToFilter(
                    'question_id',
                    ['in' => $collection->getAllIds()]
                );
            if ($ansColl->getSize()) {
                $collection->getSelect()->joinLeft(
                    $answerTable,
                    'main_table.entity_id=' . $answerTable . '.question_id',
                    [
                        'replies'=>"count(wk_mp_hotelbooking_answer.question_id)"
                    ]
                )->group('main_table.entity_id');
            } else {
                $collection->getSelect()->columns(['replies' => 0]);
            }
            $collection->setOrder('created_at');

            $this->productlists = $collection;
        }

        return $this->productlists;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllQuestions()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'marketplace.hotelbookingquestion.list.pager'
            )->setCollection(
                $this->getAllQuestions()
            );
            $this->setChild('pager', $pager);
            $this->getAllQuestions()->load();
        }

        return $this;
    }
}
