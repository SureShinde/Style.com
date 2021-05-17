<?php

namespace Webkul\Customattribute\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory;

class WeeeHelper implements ArgumentInterface
{
    /**
     * @param \Magento\Weee\Helper\Data $weeeData
     * @param CollectionFactory $orderCollection
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Webkul\Marketplace\Helper\Data $helper
     * @param \Webkul\Marketplace\Helper\Orders $orderHelper
     */
    public function __construct(
        \Magento\Weee\Helper\Data $weeeData,
        CollectionFactory $orderCollection,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Webkul\Marketplace\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Orders $orderHelper
    ) {
        $this->orderCollection = $orderCollection;
        $this->orderRepository = $orderRepository;
        $this->weeData = $weeeData;
        $this->helper = $helper;
        $this->orderHelper = $orderHelper;
    }

    /**
     * get FPT total tax
     *
     * @param [type] $items
     * @param [type] $store
     * @return void
     */
    public function getTotalAmount($items, $store)
    {
        return $this->weeData->getTotalAmounts($items, $store);
    }

    /**
     * get FPT total amount in base currency
     *
     * @param [type] $items
     * @param [type] $store
     * @return void
     */
    public function getBaseTotalAmount($items, $store)
    {
        return $this->weeData->getBaseTotalAmounts($items, $store);
    }

    /**
     * get Fpt amount accordin
     * to seller order item
     *
     * @param [type] $orderId
     * @return void
     */
    public function getOrderItems($orderId)
    {
        $rewardAmountData = $this->orderCollection->create()
        ->addFieldToFilter(
            'main_table.order_id',
            $orderId
        )->addFieldToFilter(
            'main_table.seller_id',
            $this->helper->getCustomerId()
        );
        $salesCreditmemoItem = $this->orderCollection->create()->getTable('sales_order_item');
        $rewardAmountData->getSelect()->join(
            $salesCreditmemoItem.' as creditmemo_item',
            'creditmemo_item.item_id = main_table.order_item_id'
        )->where('creditmemo_item.order_id = '.$orderId);
        $order = $this->orderRepository->get($orderId);
        $store = $order->getStore();
        $weeeTotal = $this->weeData->getTotalAmounts($rewardAmountData, $store);
        $weeeBaseTotal = $this->weeData->getBaseTotalAmounts($rewardAmountData, $store);
        $item = $this->getSource($orderId)->getFirstItem();
        $taxtoseller= $this->helper->getConfigTaxManage();
        $fpt=[];
        if (!empty($item)) {
            if (isset($taxtoseller) && $taxtoseller && $weeeBaseTotal && $item['actual_seller_amount']>0) {
                $price =  $weeeBaseTotal;
                $fpt['base_price'] = $price;
               
            } elseif (isset($taxtoseller) && $taxtoseller==1 &&
            $weeeTotal && $item['purchased_actual_seller_amount']>0) {
                $price =  $weeeTotal;
                
                $fpt['price'] = $price;
               
            }
        }
        
        return $fpt;
    }
    /**
     * get total source collection
     *
     * @param [type] $orderId
     * @return void
     */
    public function getSource($orderId)
    {
        return $this->orderCollection->create()
        ->addFieldToFilter(
            'main_table.order_id',
            $orderId
        )->addFieldToFilter(
            'main_table.seller_id',
            $this->helper->getCustomerId()
        );
    }

    /**
     * get marketplace helper
     *
     * @return void
     */
    public function getMarketplaceHelper()
    {
        return $this->helper;
    }

    /**
     * get marketplace order helper
     *
     * @return void
     */
    public function getOrderHelper()
    {
        return $this->orderHelper;
    }

    /**
     * get merged aarray
     *
     * @param [type] $array1
     * @param [type] $array2
     * @return void
     */
    public function getMergedArray($array1, $array2)
    {
        return array_merge($array1, $array2);
    }
}
