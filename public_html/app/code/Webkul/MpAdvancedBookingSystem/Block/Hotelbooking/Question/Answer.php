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

namespace Webkul\MpAdvancedBookingSystem\Block\Hotelbooking\Question;

/**
 * Webkul MpAdvancedBookingSystem Answer Block
 */
class Answer extends \Webkul\MpAdvancedBookingSystem\Block\Hotelbooking
{
    /**
     * @var \Webkul\MpAdvancedBookingSystem\Answer\Question
     */
    protected $productlists;

    /**
     * getAllAnswers
     *
     * @return bool|\Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Answer\Collection
     */
    public function getAllAnswers()
    {
        $storeId = $this->mpHelper->getCurrentStoreId();
        $websiteId = $this->mpHelper->getWebsiteId();
        if (!($customerId = $this->mpHelper->getCustomerId())) {
            return false;
        }
        if (!$this->productlists) {
            $filterParams = $this->getFilterParams();
            $questionId = $this->isValidQuestionId();
            if ($questionId) {
                $ansColl = $this->answerCollection->create()
                    ->addFieldToFilter(
                        'question_id',
                        ['eq' => $questionId]
                    );
                $ansColl->setOrder('created_at');

                if ($filterParams['filterStatus']!=='') {
                    $ansColl->addFieldToFilter(
                        'status',
                        ['eq' => $filterParams['filterStatus']]
                    );
                }
                if ($filterParams['from'] && $filterParams['to']) {
                    $ansColl->addFieldToFilter(
                        'created_at',
                        [
                            'from' => $filterParams['from'],
                            'to' => $filterParams['to'],
                            'date' => true,
                        ]
                    );
                }
                $this->productlists = $ansColl;
            } else {
                $this->messageManager->addError(
                    __("Invalid Question")
                );
                return $this->redirect->redirect(
                    $this->response,
                    'mpadvancebooking/hotelbooking/questions'
                );
            }
        }

        return $this->productlists;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllAnswers()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'marketplace.hotelbookinganswer.list.pager'
            )->setCollection(
                $this->getAllAnswers()
            );
            $this->setChild('pager', $pager);
            $this->getAllAnswers()->load();
        }

        return $this;
    }

    /**
     * isValidQuestionId
     *
     * @return boolean|int
     */
    public function isValidQuestionId()
    {
        $questionId = '';
        $paramData = $this->getRequest()->getParams();
        $sellerQuesIDs = $this->getSellerQuestionIds();
        $flag = false;
        if (!empty($paramData['question_id'])) {
            $questionId = $paramData['question_id'] != '' ? $paramData['question_id'] : '';
        }
        if ($questionId!=='') {
            if (!empty($sellerQuesIDs)
                && in_array($questionId, $sellerQuesIDs)
            ) {
                $flag = (int)$questionId;
            }
        }
        return $flag;
    }

    /**
     * getSellerQuestionIds
     *
     * @return array
     */
    private function getSellerQuestionIds()
    {
        if (!($customerId = $this->mpHelper->getCustomerId())) {
            return false;
        }
        $filterParams = $this->getFilterParams();

        /* Get Seller Product Collection for current Store Id */
        $storeProductIDs = $this->getSellerProductCollection($customerId, $filterParams['filter']);

        /* Get Seller Product Collection for 0 Store Id */
        $adminProductIDs = $this->getAdminProductCollection($customerId, $filterParams['filter']);

        $productIDs = array_merge($storeProductIDs, $adminProductIDs);

        $bookingProduct = $this->infoCollection->create();
        $allIds = $bookingProduct->getAllProductIds();
        $productIDs = array_unique(array_intersect($productIDs, $allIds));

        $collection = $this->questionCollection->create()
            ->addFieldToFilter(
                'product_id',
                ['in' => $productIDs]
            );

        return $collection->getAllIds();
    }
}
