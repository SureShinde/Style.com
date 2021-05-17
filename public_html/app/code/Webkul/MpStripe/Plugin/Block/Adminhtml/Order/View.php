<?php

namespace Webkul\MpStripe\Plugin\Block\Adminhtml\Order;

use Magento\Framework\Json\Helper\Data as JsonHelper;

class View
{
    public function __construct(
        \Webkul\MpStripe\Helper\Data $helper,
        JsonHelper $jsonHelper,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Webkul\MpStripe\Logger\StripeLogger $logger
    ) {
        $this->helper = $helper;
        $this->orderRepository = $orderRepository;
        $this->jsonHelper = $jsonHelper;
        $this->logger = $logger;
    }

    public function beforeSetLayout(\Magento\Sales\Block\Adminhtml\Order\View $view)
    {
        $message = __('Are you sure you want to do this?');
        $order = $this->orderRepository->get($view->getOrderId());
        if ($order->canInvoice() && $order->getStripePaymentIntent() != ''
        && $order->getPayment()->getMethod() == 'mpstripe') {
            $view->removeButton('order_invoice');
            $url = $view->getUrl(
                'mpstripe/capture/process',
                ['id' => $view->getOrderId(), 'payment_intent' => $order->getStripePaymentIntent()]
            );
            $view->addButton(
                'order_capture',
                [
                    'label' => __("Capture Order"),
                    'class' => 'myclass',
                    'onclick' => "confirmSetLocation('{$message}', '{$url}')"
                ]
            );
        }
    }
}
