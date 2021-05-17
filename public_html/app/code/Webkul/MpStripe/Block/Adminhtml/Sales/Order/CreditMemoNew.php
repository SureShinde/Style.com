<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpStripe
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpStripe\Block\Adminhtml\Sales\Order;

use Magento\Sales\Model\Order;

class CreditMemoNew extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    protected $request;

    protected $helper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->mpHelper = $mpHelper;
        $this->request = $request;
        parent::__construct($context, $data);
    }

    public function getSource()
    {
        return $this->_source;
    }

    public function displayFullSummary()
    {
        return true;
    }
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();
        $orderInvoiceId = $this->request->getParam('invoice_id');
        if ($orderInvoiceId) {
            if ($this->_order->getPayment()->getMethod() == \Webkul\MpStripe\Model\PaymentMethod::METHOD_CODE &&
            !$this->mpHelper->getConfigTaxManage()) {

                $invoiceData = $this->_order->getInvoiceCollection();
                foreach ($invoiceData as $invoice) {
                    $invoice_id = $invoice->getId();
                    if ($invoice_id == $orderInvoiceId) {
                        if ($invoice->getTaxAmount() == 0) {
                            $parent->removeTotal('tax');
                        }
                        $grandTotalNew = $invoice->getGrandTotal();
                        $baseGrandTotalNew = $invoice->getBaseGrandTotal();
                        $grandTotal = new \Magento\Framework\DataObject(
                            [
                                'code' => 'grand_total',
                                'strong' => true,
                                'value' => $grandTotalNew,
                                'base_value' => $baseGrandTotalNew,
                                'label' => __('Grand Total'),
                            ]
                        );
                        $parent->addTotal($grandTotal, 'grand_total');
                    }
                }

            }
            return $this;
        }
    }

    /**
     * Get order store object
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_order->getStore();
    }
    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }
}
