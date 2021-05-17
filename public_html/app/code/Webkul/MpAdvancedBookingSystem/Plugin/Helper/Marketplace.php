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
namespace Webkul\MpAdvancedBookingSystem\Plugin\Helper;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;

class Marketplace
{
    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product $product
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product $product,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
    ) {
        $this->request = $request;
        $this->collectionFactory = $collectionFactory;
        $this->product = $product;
        $this->helper = $helper;
    }

    /**
     * afterGetAllowedSets
     * runs to change the result of original function
     *
     * @param \Webkul\Marketplace\Helper\Data $subject
     * @param array $result
     * @return array
     */
    public function afterGetAllowedSets(\Webkul\Marketplace\Helper\Data $subject, $result)
    {
        try {
            $params = $this->request->getParams();

            if ((!empty($params['booking_type']) && count($result))
                || (!empty($params['id']) && $this->helper->isBookingProduct($params['id']) && count($result))
            ) {
                $attributeSet = !empty($params['set']) ? $params['set'] : (
                    !empty($params['id']) ? $this->helper->getProduct($params['id'])->getAttributeSetId() : 0
                );
                if ($attributeSet !== 0) {
                    $result = $this->collectionFactory->create()
                        ->addFieldToFilter(
                            'attribute_set_id',
                            ['eq' => $attributeSet]
                        )
                        ->setEntityTypeFilter($this->product->getTypeId())
                        ->toOptionArray();
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Plugin_Helper_Marketplace_afterGetAllowedSets Exception : ".$e->getMessage()
            );
        }
        return $result;
    }

    /**
     * afterGetAllowedAttributesetIds
     * runs to change the result of original function
     *
     * @param \Webkul\Marketplace\Helper\Data $subject
     * @param string $result
     * @return string
     */
    public function afterGetAllowedAttributesetIds(\Webkul\Marketplace\Helper\Data $subject, $result)
    {
        try {
            $params = $this->request->getParams();
            $allowedSets = [];
            if ($result) {
                $allowedSets = explode(',', $result);
            }
            $bookingProductTypes = [
                "booking",
                "hotelbooking"
            ];
            if (!empty($params['booking_product_type'])
                && !empty($params['type'])
                && !empty($params['set'])
                && in_array($params['type'], $bookingProductTypes)
                && !in_array($params['set'], $allowedSets)
            ) {
                $allowedSets[] = $params['set'];
                $result = implode(",", $allowedSets);
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Plugin_Helper_Marketplace_afterGetAllowedAttributesetIds Exception : ".$e->getMessage()
            );
        }
        return $result;
    }

    /**
     * afterGetAllowedProductType
     * runs to change the result of original function
     *
     * @param \Webkul\Marketplace\Helper\Data $subject
     * @param string $result
     * @return string
     */
    public function afterGetAllowedProductType(\Webkul\Marketplace\Helper\Data $subject, $result)
    {
        try {
            $params = $this->request->getParams();
            $allowedTypes = [];
            $bookingProductTypes = [
                "booking",
                "hotelbooking"
            ];
            if ($result) {
                $allowedTypes = explode(',', $result);
            }
            if (!empty($params['booking_product_type'])
                && !empty($params['type'])
                && in_array($params['type'], $bookingProductTypes)
                && !in_array($params['type'], $allowedTypes)
            ) {
                $allowedTypes[] = $params['type'];
                $result = implode(",", $allowedTypes);
            } elseif (!empty($params['booking_type'])
                && $params['booking_type'] == "hotel"
                && !empty($params['type'])
                && $params['type'] == "configurable"
                && !in_array($params['type'], $allowedTypes)
            ) {
                $allowedTypes[] = $params['type'];
                $result = implode(",", $allowedTypes);
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Plugin_Helper_Marketplace_afterGetAllowedProductType Exception : ".$e->getMessage()
            );
        }
        return $result;
    }

    /**
     * function runs to change the return data of GetControllerMappedPermissions.
     *
     * @param \Webkul\Marketplace\Helper\Data $helperData
     * @param array $result
     * @return bool
     */
    public function afterGetControllerMappedPermissions(
        \Webkul\Marketplace\Helper\Data $helperData,
        $result
    ) {
        $result['mpadvancebooking/product/add'] = 'mpadvancebooking/product/add';
        $result['mpadvancebooking/index/regions'] = 'mpadvancebooking/product/add';
        $result['mpadvancebooking/hotelbooking/uploadimage'] = 'mpadvancebooking/product/add';
        $result['mpadvancebooking/booking/eventChartUpload'] = 'mpadvancebooking/product/add';
        $result['mpadvancebooking/hotelbooking/updatestatus'] = 'mpadvancebooking/product/add';

        $result['mpadvancebooking/product/create'] = 'mpadvancebooking/product/create';

        $result['mpadvancebooking/product/bookinglist'] = 'mpadvancebooking/product/bookinglist';
        $result['mpadvancebooking/product/delete'] = 'mpadvancebooking/product/bookinglist';
        $result['mpadvancebooking/product_ui/delete'] = 'mpadvancebooking/product/bookinglist';

        $result['mpadvancebooking/hotelbooking/questions'] = 'mpadvancebooking/hotelbooking/questions';
        $result['mpadvancebooking/hotelbooking/updateQuestion'] = 'mpadvancebooking/hotelbooking/questions';
        $result['mpadvancebooking/hotelbooking_question/answers'] = 'mpadvancebooking/hotelbooking/questions';
        $result['mpadvancebooking/hotelbooking_question/deleteAnswer'] = 'mpadvancebooking/hotelbooking/questions';
        $result['mpadvancebooking/hotelbooking_ui/massDeleteAnswer'] = 'mpadvancebooking/hotelbooking/questions';
        $result['mpadvancebooking/hotelbooking_ui/massDeleteQuestion'] = 'mpadvancebooking/hotelbooking/questions';
        $result['mpadvancebooking/hotelbooking_ui/massStatusQuestion'] = 'mpadvancebooking/hotelbooking/questions';
        
        return $result;
    }
}
