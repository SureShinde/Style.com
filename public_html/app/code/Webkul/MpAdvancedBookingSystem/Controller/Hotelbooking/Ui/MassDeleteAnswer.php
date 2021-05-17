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

namespace Webkul\MpAdvancedBookingSystem\Controller\Hotelbooking\Ui;

use Magento\Framework\App\Action\Action;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Webkul\Marketplace\Helper\Data as HelperData;
use Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Answer\CollectionFactory as AnswerCollection;

/**
 * Webkul MpAdvancedBookingSystem MassDeleteAnswer controller.
 */
class MassDeleteAnswer extends Action
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry = null;

    /**
     * @var HelperData
     */
    private $mpHelper;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param Session $customerSession
     * @param Registry $coreRegistry
     * @param HelperData $mpHelper
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
     * @param AnswerCollection $answerCollection
     * @param \Magento\Customer\Model\Url $customerUrl
     */
    public function __construct(
        Context $context,
        Filter $filter,
        Session $customerSession,
        Registry $coreRegistry,
        HelperData $mpHelper,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper,
        AnswerCollection $answerCollection,
        \Magento\Customer\Model\Url $customerUrl
    ) {
        $this->filter = $filter;
        $this->customerSession = $customerSession;
        $this->coreRegistry = $coreRegistry;
        $this->mpHelper = $mpHelper;
        $this->helper = $helper;
        $this->answerCollection = $answerCollection;
        $this->customerUrl = $customerUrl;
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->customerUrl->getLoginUrl();

        if (!$this->customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Mass delete seller products action.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $isPartner = $this->mpHelper->isSeller();
        if ($isPartner == 1) {
            try {
                $collection = $this->filter->getCollection(
                    $this->answerCollection->create()
                );
                $ids = $collection->getAllIds();
                $flag = false;
                $questionId = 0;

                $this->coreRegistry->register('isSecureArea', 1);
                $sellerProducts = $this->answerCollection->create()
                    ->addFieldToFilter(
                        'entity_id',
                        ['in' => $ids]
                    );
                $totalRecords = $sellerProducts->getSize();
                $questionIds = array_unique($sellerProducts->getColumnValues('question_id'));

                if ($sellerProducts->getSize()
                    && count($questionIds) == 1
                    && !empty($questionIds[0])
                ) {
                    $questionId = $questionIds[0];
                    $sellerProducts->walk('delete');
                    $flag = true;
                }
                $this->coreRegistry->unregister('isSecureArea');

                // clear cache
                $this->mpHelper->clearCache();
                if ($flag) {
                    $this->messageManager->addSuccess(
                        __('%1 Records are succesfully deleted from your account.', $totalRecords)
                    );
                } else {
                    $this->messageManager->addError(
                        __('Something went wrong !!!')
                    );
                }
            } catch (\Exception $e) {
                $this->helper->logDataInLogger(
                    "Controller_Hotelbooking_Ui_MassDeleteAnswer_execute Exception : ".$e->getMessage()
                );
                $this->messageManager->addError($e->getMessage());
            }
            if ($questionId) {
                return $this->resultRedirectFactory->create()->setPath(
                    'mpadvancebooking/hotelbooking_question/answers',
                    [
                        '_secure' => $this->getRequest()->isSecure(),
                        'question_id' => $questionId
                    ]
                );
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    'mpadvancebooking/hotelbooking/questions',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
