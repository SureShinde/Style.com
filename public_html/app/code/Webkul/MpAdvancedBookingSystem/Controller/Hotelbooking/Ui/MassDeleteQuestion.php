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
use Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Question\CollectionFactory as QuestionCollection;

/**
 * Webkul MpAdvancedBookingSystem MassDeleteQuestion controller.
 */
class MassDeleteQuestion extends Action
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
     * @param QuestionCollection $questionCollection
     * @param \Magento\Customer\Model\Url $customerUrl
     */
    public function __construct(
        Context $context,
        Filter $filter,
        Session $customerSession,
        Registry $coreRegistry,
        HelperData $mpHelper,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper,
        QuestionCollection $questionCollection,
        \Magento\Customer\Model\Url $customerUrl
    ) {
        $this->filter = $filter;
        $this->customerSession = $customerSession;
        $this->coreRegistry = $coreRegistry;
        $this->mpHelper = $mpHelper;
        $this->helper = $helper;
        $this->questionCollection = $questionCollection;
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
                    $this->questionCollection->create()
                );
                $ids = $collection->getAllIds();

                $this->coreRegistry->register('isSecureArea', 1);
                $sellerProducts = $this->questionCollection->create()
                    ->addFieldToFilter(
                        'entity_id',
                        ['in' => $ids]
                    );
                $totalRecords = $sellerProducts->getSize();
                if ($sellerProducts->getSize()) {
                    $sellerProducts->walk('delete');
                }
                $this->coreRegistry->unregister('isSecureArea');

                // clear cache
                $this->mpHelper->clearCache();
                $this->messageManager->addSuccess(
                    __('%1 Records are succesfully deleted from your account.', $totalRecords)
                );
            } catch (\Exception $e) {
                $this->helper->logDataInLogger(
                    "Controller_Hotelbooking_Ui_MassDeleteQuestion_execute Exception : ".$e->getMessage()
                );
                $this->messageManager->addError($e->getMessage());
            }
            return $this->resultRedirectFactory->create()->setPath(
                'mpadvancebooking/hotelbooking/questions',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
