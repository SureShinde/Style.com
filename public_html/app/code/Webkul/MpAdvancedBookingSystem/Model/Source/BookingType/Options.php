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
namespace Webkul\MpAdvancedBookingSystem\Model\Source\BookingType;

use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Eav\Model\Config as EavConfig;

class Options implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var null|array
     */
    protected $options;

    /**
     * EAV Attribute Set Factory.
     *
     * @var SetFactory
     */
    private $attributeSetFactory;

    /**
     * EAV Config.
     *
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @param SetFactory $attributeSetFactory
     * @param EavConfig  $eavConfig
     */
    public function __construct(
        SetFactory $attributeSetFactory,
        EavConfig $eavConfig
    ) {
        $this->attributeSetFactory = $attributeSetFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * @return array|null
     */
    public function toOptionArray()
    {
        $entityType = $this->eavConfig->getEntityType(
            \Magento\Catalog\Model\Product::ENTITY
        );
        $entityTypeId = $entityType->getId();
        $attributeSet = $this->attributeSetFactory->create();
        $setCollection = $attributeSet->getResourceCollection()
            ->addFieldToFilter('entity_type_id', $entityTypeId)
            ->addFieldToFilter('attribute_set_name', 'Appointment Booking')
            ->load();
        $attributeSet = $setCollection->fetchItem();
        $optionsData = [];
        if ($attributeSet) {
            $optionsData[0]['value'] = $attributeSet->getId();
            $optionsData[0]['label'] = 'Appointment Booking';
        }

        $attributeSet = $this->attributeSetFactory->create();
        $setCollection = $attributeSet->getResourceCollection()
            ->addFieldToFilter('entity_type_id', $entityTypeId)
            ->addFieldToFilter('attribute_set_name', 'Rental Booking')
            ->load();
        $attributeSet = $setCollection->fetchItem();
        if ($attributeSet) {
            $optionsData[1]['value'] = $attributeSet->getId();
            $optionsData[1]['label'] = 'Rental Booking';
        }

        $attributeSet = $this->attributeSetFactory->create();
        $setCollection = $attributeSet->getResourceCollection()
            ->addFieldToFilter('entity_type_id', $entityTypeId)
            ->addFieldToFilter('attribute_set_name', 'Event Booking')
            ->load();
        $attributeSet = $setCollection->fetchItem();
        if ($attributeSet) {
            $optionsData[2]['value'] = $attributeSet->getId();
            $optionsData[2]['label'] = 'Event Booking';
        }

        $attributeSet = $this->attributeSetFactory->create();
        $setCollection = $attributeSet->getResourceCollection()
            ->addFieldToFilter('entity_type_id', $entityTypeId)
            ->addFieldToFilter('attribute_set_name', 'Hotel Booking')
            ->load();
        $attributeSet = $setCollection->fetchItem();
        if ($attributeSet) {
            $optionsData[3]['value'] = $attributeSet->getId();
            $optionsData[3]['label'] = 'Hotel Booking';
        }

        $attributeSet = $this->attributeSetFactory->create();
        $setCollection = $attributeSet->getResourceCollection()
            ->addFieldToFilter('entity_type_id', $entityTypeId)
            ->addFieldToFilter('attribute_set_name', 'Table Booking')
            ->load();
        $attributeSet = $setCollection->fetchItem();
        if ($attributeSet) {
            $optionsData[4]['value'] = $attributeSet->getId();
            $optionsData[4]['label'] = 'Table Booking';
        }

        $attributeSet = $this->attributeSetFactory->create();
        $setCollection = $attributeSet->getResourceCollection()
            ->addFieldToFilter('entity_type_id', $entityTypeId)
            ->addFieldToFilter('attribute_set_name', 'Default')
            ->load();
        $attributeSet = $setCollection->fetchItem();
        if ($attributeSet) {
            $optionsData[5]['value'] = $attributeSet->getId();
            $optionsData[5]['label'] = 'Default';
        }

        $this->options = $optionsData;
        return $this->options;
    }
}
