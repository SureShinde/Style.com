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

namespace Webkul\MpAdvancedBookingSystem\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Webkul\Marketplace\Helper\Notification as NotificationHelper;
use Webkul\Marketplace\Model\Notification;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory;

/**
 * Webkul MpAdvancedBookingSystem Bookinglist controller.
 */
class Bookinglist extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    private $helperData;

    /**
     * @var NotificationHelper
     */
    private $notificationHelper;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\Marketplace\Helper\Data $helperData
     * @param NotificationHelper $notificationHelper
     * @param CollectionFactory $collectionFactory
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
     * @param \Magento\Customer\Model\Url $customerUrl
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Marketplace\Helper\Data $helperData,
        NotificationHelper $notificationHelper,
        CollectionFactory $collectionFactory,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper,
        \Magento\Customer\Model\Url $customerUrl
    ) {
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->helperData = $helperData;
        $this->notificationHelper = $notificationHelper;
        $this->collectionFactory = $collectionFactory;
        $this->helper = $helper;
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
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $helper = $this->helperData;
        $isPartner = $helper->isSeller();
        $sellerDashboardUrl = $this->resultRedirectFactory->create()->setPath(
            'marketplace/account/dashboard',
            ['_secure' => $this->getRequest()->isSecure()]
        );
        if ($isPartner == 1) {
            if (!$this->helper->getMpConfigValue('enable_booking')) {
                return $sellerDashboardUrl;
            }
            $resultPage = $this->resultPageFactory->create();
            if ($helper->getIsSeparatePanel()) {
                $resultPage->addHandle('mpadvancebooking_layout2_product_bookinglist');
            }
            $resultPage->getConfig()->getTitle()->set(
                __('Marketplace Booking Product List')
            );
            /**
             * update notification for products
             */
            $collection = $this->collectionFactory->create()
                ->addFieldToFilter(
                    'seller_id',
                    $helper->getCustomerId()
                )->addFieldToFilter(
                    'seller_pending_notification',
                    1
                );
            if ($collection->getSize()) {
                $type = Notification::TYPE_PRODUCT;
                $this->notificationHelper->updateNotificationCollection(
                    $collection,
                    $type
                );
            }
            return $resultPage;
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
