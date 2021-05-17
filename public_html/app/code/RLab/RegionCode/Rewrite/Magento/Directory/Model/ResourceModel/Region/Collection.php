<?php
declare(strict_types=1);

namespace RLab\RegionCode\Rewrite\Magento\Directory\Model\ResourceModel\Region;

class Collection extends \Magento\Directory\Model\ResourceModel\Region\Collection
{

    public function toOptionArray()
    {
        $options = [];
        $propertyMap = [
            'value' => 'region_id',
            'title' => 'default_name',
            'country_id' => 'country_id',
        ];

        foreach ($this as $item) {
            $option = [];
            foreach ($propertyMap as $code => $field) {
                $option[$code] = $item->getData($field);
            }
            // $option['label'] = $item->getName();
            $option['label'] = $item->getCode();
            $options[] = $option;
        }

        if (count($options) > 0) {
            array_unshift(
                $options,
                ['title' => '', 'value' => '', 'label' => __('Please select a region, state or province.')]
            );
        }
        return $options;
    }
}
