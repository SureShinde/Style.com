<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpStripe
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpStripe\Controller\Order;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Webkul\MpStripe\Controller\Payment\CreateIntent;
use Magento\Framework\Exception\LocalizedException;

class Capture extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;
    /**
     * @var Magento\Customer\Model\Session
     */
    private $customerSession;

    private $helper;

    private $marketplaceHelper;

    /**
     * @var \Magento\Customer\Model\Url
     */
    private $customerUrl;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param \Webkul\MpStripe\Helper\Data $helper
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     * @param \Magento\Customer\Model\Url $customerUrl
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        \Webkul\MpStripe\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Psr\Log\LoggerInterface $logger,
        CreateIntent $createIntent,
        \Magento\Sales\Model\OrderRepository $orderRepository
    ) {
        $this->helper = $helper;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->orderRepository = $orderRepository;
        $this->_logger = $logger;
        $this->createIntent = $createIntent;
        parent::__construct($context);
    }

    /**
     * Connect to stripe.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $request = $this->getRequest()->getParams();
        $orderId = $request['id'];
        $order = $this->orderRepository->get($orderId);
        if (!$order->canInvoice()) {

            $this->messageManager->addError(
                __('The order has already been captured successfully')
            );
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/order/view',
                ['id' => $orderId, '_secure' => $this->getRequest()->isSecure()]
            );
        }
        $paymentIntent = $request['payment_intent'];
        $this->helper->setUpDefaultDetails();
        $intent = \Stripe\PaymentIntent::retrieve($paymentIntent);
        $finalCart = $this->helper->getFinalCart($order);
        $finalCartData = $this->helper->getCheckoutFinalData($finalCart, $order);
        $ifSellerInCart = $this->helper->getIfSellerInCart($finalCartData);
        $paymentIntent = \Stripe\PaymentIntent::retrieve(
            $paymentIntent
        );
        try {
            $captureData = $paymentIntent->capture();
            $response = [];
            if ($ifSellerInCart) {
                foreach ($finalCartData as $sellerId => $paymentDetail) {
                    if (!empty($paymentDetail['cart']['stripe_user_id'])) {
                        $response[$sellerId] = $this->createIntent->createStripeTransferCharge(
                            $paymentDetail,
                            $paymentIntent['charges']['data'][0]
                        );
                    }
                }
            }
            $this->messageManager->addSuccess(
                __('Order captured successfully, invoices will be generated automatically')
            );
        } catch (\Exception $e) {
            $this->_logger->info('capture controller '.$e->getMessage());
            $this->messageManager->addError(
                __('There was an error capturing the transaction')
            );
        }

        return $this->resultRedirectFactory->create()->setPath(
            'marketplace/order/view',
            ['id' => $orderId, '_secure' => $this->getRequest()->isSecure()]
        );
    }
}
