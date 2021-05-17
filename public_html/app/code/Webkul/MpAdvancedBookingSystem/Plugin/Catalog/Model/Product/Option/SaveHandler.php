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
namespace Webkul\MpAdvancedBookingSystem\Plugin\Catalog\Model\Product\Option;

use \Magento\Framework\App\ResourceConnection;
use \Webkul\MpAdvancedBookingSystem\Logger\Logger;

class SaveHandler
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;
    
    /**
     * @var \Webkul\MpAdvancedBookingSystem\Logger\Logger
     */
    protected $mpBookingLogger;
    
    public function __construct(
        ResourceConnection $resource,
        Logger $mpBookingLogger
    ) {
        $this->resource = $resource;
        $this->mpBookingLogger = $mpBookingLogger;
    }
    /**
     * function to run to change the custom option value.
     *
     * @param \Magento\Catalog\Model\Product\Option\SaveHandler $subject
     * @param object $entity
     * @param array $arguments
     *
     * @return array
     */
    public function beforeExecute(
        \Magento\Catalog\Model\Product\Option\SaveHandler $subject,
        $entity,
        $arguments = []
    ) {
        $options = $entity->getOptions();
        if ($options) {
            foreach ($options as $option) {
                $optionData = $option->getData();
                if (isset($optionData['record_id']) && $optionData['record_id'] > 0) {
                    $optionData['option_id'] = $optionData['record_id'];
                }
                if (($option->getOptionId() === null) && !empty($option->getProductId())) {
                    $option->setOptionId($option->getRecordId());
                    if ($option->hasValues() && $option->getData('values')) {
                        $values = $optionData['values'];
                        foreach ($option->getData('values') as $key => $value) {
                            if ($value['option_id'] == "") {
                                $values[$key]['record_id'] = $key;
                                empty($values[$key]['is_delete']);
                            }
                        }
                        $optionData['values'] = $values;
                        $option->setData('values', $values);
                        $flag = true;
                    }
                } else {
                    if ($option->hasValues() && $option->getData('values')) {
                        foreach ($option->getData('values') as $key) {
                            if (isset($key['option_type_id'])) {
                                $optionTypeId = $key['option_type_id'];
                                $price = $key['price'];
                                $priceType = $key['price_type'];
                                $this->savePrice($optionTypeId, $price, $priceType);
                            }
                        }
                    }
                }
                $option->setData($optionData);
            }
            $entity->setOptions($options);
        }
        return [$entity, $arguments];
    }

    public function savePrice($optionTypeId, $price, $priceType)
    {
        try {
            $storeId = 0;
            $resource = $this->resource;
            $priceTable = $resource->getTableName('catalog_product_option_type_price');

            $select = $resource->getConnection()->select()->from(
                $priceTable,
                'option_type_id'
            )->where(
                'option_type_id = ?',
                $optionTypeId
            )->where(
                'store_id = ?',
                $storeId
            );
            if ($optionTypeId) {
                $optionTypeId = $resource->getConnection()->fetchOne($select);
                $bind = ['price' => $price, 'price_type' => $priceType];
                $where = [
                    'option_type_id = ?' => $optionTypeId,
                    'store_id = ?' => 0,
                ];
    
                $resource->getConnection()->update($priceTable, $bind, $where);
            }
        } catch (\Exception $e) {
            $this->mpBookingLogger->critical("SaveHandler Exception : ".$e->getMessage());
        }
    }
}
