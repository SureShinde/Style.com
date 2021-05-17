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
namespace Webkul\MpStripe\Controller\Adminhtml\Capture;

use Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Catalog\Model\Product\Visibility;
use Magento\Store\Model\StoreManagerInterface;
use Webkul\MpStripe\Controller\Payment\CreateIntent;
use Magento\Framework\Exception\LocalizedException;

class Process extends \Magento\Backend\App\Action
{
    protected $_logger;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Webkul\MpStripe\Helper\Data $helper
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\Url $urlHelper
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Webkul\MpStripe\Helper\Data $helper,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Url $urlHelper,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Psr\Log\LoggerInterface $logger,
        CreateIntent $createIntent
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->messageManager = $messageManager;
        $this->_logger = $logger;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->configWriter = $configWriter;
        $this->orderRepository = $orderRepository;
        $this->storeManager = $storeManager;
        $this->urlHelper = $urlHelper;
        $this->helper = $helper;
        $this->createIntent = $createIntent;
        parent::__construct($context);
    }

    /**
     * execute webhook creation
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
                'sales/order/view',
                ['order_id' => $orderId, '_secure' => $this->getRequest()->isSecure()]
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
            'sales/order/view',
            ['order_id' => $orderId, '_secure' => $this->getRequest()->isSecure()]
        );
    }
}
