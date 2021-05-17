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
namespace Webkul\MpAdvancedBookingSystem\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class ProductAttributes implements DataPatchInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * @var \Magento\Eav\Setup\EavSetupFactory;
     */
    private $eavSetupFactory;
    
    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    private $attributeSetFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavConfig $eavConfig
     * @param EavSetupFactory $eavSetupFactory
     * @param SetFactory $attributeSetFactory
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavConfig $eavConfig,
        EavSetupFactory $eavSetupFactory,
        SetFactory $attributeSetFactory,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavConfig = $eavConfig;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->helper = $helper;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        try {
            $this->moduleDataSetup->getConnection()->startSetup();

            /* Create Attribute Set */
            $bookingAttributeSets = [
                "appointment" => ['name' => 'Appointment Booking'],
                "rental" => ['name' => 'Rental Booking'],
                "event" => ['name' => 'Event Booking'],
                "hotel" => ['name' => 'Hotel Booking'],
                "table" => ['name' => 'Table Booking']
            ];

            foreach ($bookingAttributeSets as $bookingAttributeSetType => $bookingAttributeSetData) {
                $attributeSet = $this->createAttributeSet($bookingAttributeSetData['name']);
                $bookingAttributeSets[$bookingAttributeSetType]['id'] = $attributeSet->getId();
                $bookingAttributeSets[$bookingAttributeSetType]['group_id'] = $attributeSet->getDefaultGroupId();
            }

            /* Create Universal Booking Attributes*/
            $entityType = $this->eavConfig->getEntityType(Product::ENTITY);
            $entityTypeId = $entityType->getId();
            $bookingAttributes = [];

            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
            $eavSetup->addAttribute(
                Product::ENTITY,
                'phone_number',
                [
                    'type' => 'varchar',
                    'group' => '',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Contact Number',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => false,
                    'required' => true,
                    'user_defined' => false,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
            $phoneNoAttributeId = $eavSetup->getAttributeId($entityTypeId, 'phone_number');
            $bookingAttributes['all'][] = $phoneNoAttributeId;

            $eavSetup->addAttribute(
                Product::ENTITY,
                'location',
                [
                    'type' => 'varchar',
                    'group' => '',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Location',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => false,
                    'required' => true,
                    'user_defined' => true,
                    'searchable' => true,
                    'filterable' => false,
                    'comparable' => true,
                    'visible_on_front' => true,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
            $locationAttributeId = $eavSetup->getAttributeId($entityTypeId, 'location');
            $bookingAttributes['all'][] = $locationAttributeId;

            $eavSetup->addAttribute(
                Product::ENTITY,
                'show_map_loction',
                [
                    'type' => 'int',
                    'group' => '',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Show Map with Location',
                    'input' => 'boolean',
                    'class' => '',
                    'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                    'default' => 1,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => false,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
            $showMapAttributeId = $eavSetup->getAttributeId($entityTypeId, 'show_map_loction');
            $bookingAttributes['all'][] = $showMapAttributeId;

            $eavSetup->addAttribute(
                Product::ENTITY,
                'show_contact_button_to',
                [
                    'type' => 'int',
                    'group' => '',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Show Contact Button to',
                    'input' => 'select',
                    'class' => '',
                    'source' => \Webkul\MpAdvancedBookingSystem\Model\Source\ShowContactButtonToOptions::class,
                    'default' => 1,
                    'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                    'visible' => false,
                    'required' => true,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
            $showContactBtnToAttributeId = $eavSetup->getAttributeId(
                $entityTypeId,
                'show_contact_button_to'
            );
            $bookingAttributes['all'][] = $showContactBtnToAttributeId;

            /* Create Attributes for Appointment Type Booking */
            
            $eavSetup->addAttribute(
                Product::ENTITY,
                'slot_duration',
                [
                    'type' => 'varchar',
                    'group' => '',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Slot Duration(Mins)',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => false,
                    'required' => true,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
            $slotDurationAttrId = $eavSetup->getAttributeId($entityTypeId, 'slot_duration');
            $bookingAttributes['appointment'][] = $slotDurationAttrId;
            $bookingAttributes['table'][] = $slotDurationAttrId;

            $eavSetup->addAttribute(
                Product::ENTITY,
                'break_time_bw_slot',
                [
                    'type' => 'varchar',
                    'group' => '',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Break Time b/w Slots(Mins)',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => false,
                    'required' => true,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
            $breakTimeBwSlotAttrId = $eavSetup->getAttributeId(
                $entityTypeId,
                'break_time_bw_slot'
            );
            $bookingAttributes['appointment'][] = $breakTimeBwSlotAttrId;
            $bookingAttributes['table'][] = $breakTimeBwSlotAttrId;

            $eavSetup->addAttribute(
                Product::ENTITY,
                'prevent_scheduling_before',
                [
                    'type' => 'varchar',
                    'group' => '',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Prevent Scheduling(Mins)',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => false,
                    'required' => true,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
            $preventSchedulingAttrId = $eavSetup->getAttributeId(
                $entityTypeId,
                'prevent_scheduling_before'
            );
            $bookingAttributes['appointment'][] = $preventSchedulingAttrId;
            $bookingAttributes['table'][] = $preventSchedulingAttrId;
            $bookingAttributes['rental'][] = $preventSchedulingAttrId;

            $eavSetup->addAttribute(
                Product::ENTITY,
                'available_every_week',
                [
                    'type' => 'int',
                    'group' => '',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Available Every Week',
                    'input' => 'boolean',
                    'class' => '',
                    'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                    'default' => 1,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => false,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
            $availableEveryWeekAttrId = $eavSetup->getAttributeId(
                $entityTypeId,
                'available_every_week'
            );
            $bookingAttributes['appointment'][] = $availableEveryWeekAttrId;
            $bookingAttributes['rental'][] = $availableEveryWeekAttrId;

            $eavSetup->addAttribute(
                Product::ENTITY,
                'booking_available_from',
                [
                    'type' => 'varchar',
                    'group' => '',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Available From',
                    'input' => 'date',
                    'class' => '',
                    'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                    'default' => 1,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => false,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
            $availableFromAttrId = $eavSetup->getAttributeId(
                $entityTypeId,
                'booking_available_from'
            );
            $bookingAttributes['appointment'][] = $availableFromAttrId;
            $bookingAttributes['rental'][] = $availableFromAttrId;

            $eavSetup->addAttribute(
                Product::ENTITY,
                'booking_available_to',
                [
                    'type' => 'varchar',
                    'group' => '',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Available To',
                    'input' => 'date',
                    'class' => '',
                    'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                    'default' => 1,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => false,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
            $availableToAttrId = $eavSetup->getAttributeId(
                $entityTypeId,
                'booking_available_to'
            );
            $bookingAttributes['appointment'][] = $availableToAttrId;
            $bookingAttributes['rental'][] = $availableToAttrId;

            $eavSetup->addAttribute(
                Product::ENTITY,
                'slot_for_all_days',
                [
                    'type' => 'int',
                    'group' => '',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Same Slot for All Days',
                    'input' => 'boolean',
                    'class' => '',
                    'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                    'default' => 1,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => false,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
            $slotForAllDaysAttrId = $eavSetup->getAttributeId(
                $entityTypeId,
                'slot_for_all_days'
            );
            $bookingAttributes['appointment'][] = $slotForAllDaysAttrId;
            $bookingAttributes['rental'][] = $slotForAllDaysAttrId;
            $bookingAttributes['table'][] = $slotForAllDaysAttrId;

            $eavSetup->addAttribute(
                Product::ENTITY,
                'slot_has_quantity',
                [
                    'type' => 'int',
                    'group' => '',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Slot has Quantity',
                    'input' => 'boolean',
                    'class' => '',
                    'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                    'default' => 1,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => false,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
            $slotHasQtyAttrId = $eavSetup->getAttributeId(
                $entityTypeId,
                'slot_has_quantity'
            );
            $bookingAttributes['appointment'][] = $slotHasQtyAttrId;
            $bookingAttributes['rental'][] = $slotHasQtyAttrId;

            /* Create Attributes for Rental Type Booking */

            $eavSetup->addAttribute(
                Product::ENTITY,
                'renting_type',
                [
                    'type' => 'int',
                    'group' => '',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Renting Type',
                    'input' => 'select',
                    'class' => '',
                    'source' => \Webkul\MpAdvancedBookingSystem\Model\Source\RentingTypeOptions::class,
                    'default' => 1,
                    'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                    'visible' => false,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
            $rentingTypeAttrId = $eavSetup->getAttributeId(
                $entityTypeId,
                'renting_type'
            );
            $bookingAttributes['rental'][] = $rentingTypeAttrId;

            /* Create Attributes for Event Type Booking */
            
            $eavSetup->addAttribute(
                Product::ENTITY,
                'event_chart_available',
                [
                    'type' => 'int',
                    'group' => '',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Event Map/Chart Available',
                    'input' => 'boolean',
                    'class' => '',
                    'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                    'default' => 1,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => false,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
            $eventChartAvailableAttrId = $eavSetup->getAttributeId(
                $entityTypeId,
                'event_chart_available'
            );
            $bookingAttributes['event'][] = $eventChartAvailableAttrId;

            $eavSetup->addAttribute(
                Product::ENTITY,
                'event_chart_image',
                [
                    'type' => 'varchar',
                    'group' => '',
                    'backend' => \Webkul\MpAdvancedBookingSystem\Model\Source\Product\Attribute\Backend\Image::class,
                    'frontend' => '',
                    'label' => 'Event Chart Image',
                    'input' => 'image',
                    'class' => '',
                    'source' => '',
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => false,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
            $eventChartImageAttrId = $eavSetup->getAttributeId(
                $entityTypeId,
                'event_chart_image'
            );
            $bookingAttributes['event'][] = $eventChartImageAttrId;

            $eavSetup->addAttribute(
                Product::ENTITY,
                'price_charged_per',
                [
                    'type' => 'int',
                    'group' => '',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Price Charged Per',
                    'input' => 'select',
                    'class' => '',
                    'source' => \Webkul\MpAdvancedBookingSystem\Model\Source\PriceChargedPerOptions::class,
                    'default' => 1,
                    'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                    'visible' => false,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
            $priceChargedPerAttrId = $eavSetup->getAttributeId(
                $entityTypeId,
                'price_charged_per'
            );
            $bookingAttributes['event'][] = $priceChargedPerAttrId;
            $bookingAttributes['hotel'][] = $priceChargedPerAttrId;
            
            $eavSetup->addAttribute(
                Product::ENTITY,
                'is_multiple_tickets',
                [
                    'type' => 'int',
                    'group' => '',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Event Map/Chart Available',
                    'input' => 'boolean',
                    'class' => '',
                    'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                    'default' => 1,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => false,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
            $isMulipleTicketsAttrId = $eavSetup->getAttributeId(
                $entityTypeId,
                'is_multiple_tickets'
            );
            $bookingAttributes['event'][] = $isMulipleTicketsAttrId;

            $eavSetup->addAttribute(
                Product::ENTITY,
                'event_date_from',
                [
                    'type' => 'varchar',
                    'group' => '',
                    'backend' => \Magento\Eav\Model\Entity\Attribute\Backend\Datetime::class,
                    'frontend' => '',
                    'label' => 'Event Date From',
                    'input' => 'datetime',
                    'class' => '',
                    'source' => '',
                    'default' => 1,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => false,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
            $eventDateFromAttrId = $eavSetup->getAttributeId(
                $entityTypeId,
                'event_date_from'
            );
            $bookingAttributes['event'][] = $eventDateFromAttrId;

            $eavSetup->addAttribute(
                Product::ENTITY,
                'event_date_to',
                [
                    'type' => 'varchar',
                    'group' => '',
                    'backend' => \Magento\Eav\Model\Entity\Attribute\Backend\Datetime::class,
                    'frontend' => '',
                    'label' => 'Event Date To',
                    'input' => 'datetime',
                    'class' => '',
                    'source' => '',
                    'default' => 1,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => false,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
            $eventDateToAttrId = $eavSetup->getAttributeId(
                $entityTypeId,
                'event_date_to'
            );
            $bookingAttributes['event'][] = $eventDateToAttrId;

            /* Create Attributes for Hotel Type Booking */

            $eavSetup->addAttribute(
                Product::ENTITY,
                'ask_a_ques_enable',
                [
                    'type' => 'int',
                    'group' => '',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Enable Ask a Question',
                    'input' => 'boolean',
                    'class' => '',
                    'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                    'default' => 1,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => false,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
            $enableAskQuestionAttrId = $eavSetup->getAttributeId(
                $entityTypeId,
                'ask_a_ques_enable'
            );
            $bookingAttributes['hotel'][] = $enableAskQuestionAttrId;

            $eavSetup->addAttribute(
                Product::ENTITY,
                'show_nearby_map',
                [
                    'type' => 'int',
                    'group' => '',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Show Nearby Map',
                    'input' => 'boolean',
                    'class' => '',
                    'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                    'default' => 1,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => false,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
            $showNearbyAttrId = $eavSetup->getAttributeId(
                $entityTypeId,
                'show_nearby_map'
            );
            $bookingAttributes['hotel'][] = $showNearbyAttrId;

            $eavSetup->addAttribute(
                Product::ENTITY,
                'hotel_address',
                [
                    'type' => 'varchar',
                    'group' => '',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Address',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => false,
                    'required' => true,
                    'user_defined' => true,
                    'searchable' => true,
                    'filterable' => false,
                    'comparable' => true,
                    'visible_on_front' => true,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
            $hotelAddressAttrId = $eavSetup->getAttributeId(
                $entityTypeId,
                'hotel_address'
            );
            $bookingAttributes['hotel'][] = $hotelAddressAttrId;
            $bookingAttributes['table'][] = $hotelAddressAttrId;

            $eavSetup->addAttribute(
                Product::ENTITY,
                'hotel_country',
                [
                    'type' => 'varchar',
                    'group' => '',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Country',
                    'input' => 'select',
                    'class' => '',
                    'source' => \Webkul\MpAdvancedBookingSystem\Model\Source\Country::class,
                    'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                    'visible' => false,
                    'required' => true,
                    'user_defined' => true,
                    'searchable' => true,
                    'filterable' => false,
                    'comparable' => true,
                    'visible_on_front' => true,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
            $hotelCountryAttributeId = $eavSetup->getAttributeId(
                $entityTypeId,
                'hotel_country'
            );
            $bookingAttributes['hotel'][] = $hotelCountryAttributeId;
            $bookingAttributes['table'][] = $hotelCountryAttributeId;

            $eavSetup->addAttribute(
                Product::ENTITY,
                'hotel_state',
                [
                    'type' => 'varchar',
                    'group' => '',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'State',
                    'input' => 'text',
                    'class' => '',
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => false,
                    'required' => true,
                    'user_defined' => true,
                    'searchable' => true,
                    'filterable' => false,
                    'comparable' => true,
                    'visible_on_front' => true,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
            $hotelStateAttributeId = $eavSetup->getAttributeId(
                $entityTypeId,
                'hotel_state'
            );
            $bookingAttributes['hotel'][] = $hotelStateAttributeId;
            $bookingAttributes['table'][] = $hotelStateAttributeId;

            $eavSetup->addAttribute(
                Product::ENTITY,
                'price_charged_per_hotel',
                [
                    'type' => 'int',
                    'group' => '',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Price Charged Per',
                    'input' => 'select',
                    'class' => '',
                    'source' => \Webkul\MpAdvancedBookingSystem\Model\Source\PriceChargedPerOptionsHotel::class,
                    'default' => 1,
                    'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                    'visible' => false,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
            $priceChargedPerHotelAttrId = $eavSetup->getAttributeId(
                $entityTypeId,
                'price_charged_per_hotel'
            );
            $bookingAttributes['hotel'][] = $priceChargedPerHotelAttrId;
            
            $attributeId = $eavSetup->getAttribute(Product::ENTITY, 'amenities', 'attribute_id');
            if (!$attributeId) {
                $eavSetup->addAttribute(
                    Product::ENTITY,
                    'amenities',
                    [
                        'type' => 'varchar',
                        'group' => '',
                        'frontend' => '',
                        'label' => 'Amenities',
                        'input' => 'multiselect',
                        'class' => '',
                        'backend' => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
                        'default' => 1,
                        'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                        'visible' => true,
                        'required' => false,
                        'user_defined' => true,
                        'searchable' => true,
                        'filterable' => true,
                        'comparable' => true,
                        'visible_on_front' => true,
                        'used_in_product_listing' => false,
                        'unique' => false,
                        'option' => [
                            'values' => [
                                'Parking',
                                'Beds',
                                'Garden',
                                'Doorstep Doctor',
                                'Free Cycle',
                                'Shower',
                                'Guards And Security',
                                'Doorstep Electrician',
                                'Fixed Phone Line',
                                'Power Backup',
                                'Pets Allowed'
                            ],
                        ],
                    ]
                );
                $amenitiesAttrId = $eavSetup->getAttributeId(
                    $entityTypeId,
                    'amenities'
                );
                $bookingAttributes['hotel'][] = $amenitiesAttrId;
            }

            $attributeId = $eavSetup->getAttribute(Product::ENTITY, 'room_type', 'attribute_id');
            if (!$attributeId) {
                $eavSetup->addAttribute(
                    Product::ENTITY,
                    'room_type',
                    [
                        'type' => 'int',
                        'group' => '',
                        'frontend' => '',
                        'label' => 'Room Type',
                        'input' => 'select',
                        'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                        'visible' => true,
                        'required' => false,
                        'user_defined' => true,
                        'searchable' => true,
                        'filterable' => true,
                        'comparable' => true,
                        'visible_on_front' => false,
                        'used_in_product_listing' => true,
                        'unique' => false,
                        'option' => [
                            'values' => [
                                'Mini Suite',
                                'Queen Suite',
                                'King Suite',
                            ],
                        ],
                    ]
                );
            }

            $eavSetup->addAttribute(
                Product::ENTITY,
                'check_in_time',
                [
                    'type' => 'varchar',
                    'group' => '',
                    'frontend' => '',
                    'label' => 'Check In',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => false,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
            $checkInTimeAttrId = $eavSetup->getAttributeId(
                $entityTypeId,
                'check_in_time'
            );
            $bookingAttributes['hotel'][] = $checkInTimeAttrId;

            $eavSetup->addAttribute(
                Product::ENTITY,
                'check_out_time',
                [
                    'type' => 'varchar',
                    'group' => '',
                    'frontend' => '',
                    'label' => 'Check Out',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => false,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
            $checkOutTimeAttrId = $eavSetup->getAttributeId(
                $entityTypeId,
                'check_out_time'
            );
            $bookingAttributes['hotel'][] = $checkOutTimeAttrId;

            /* Create Attributes for Table Type Booking */

            $eavSetup->addAttribute(
                Product::ENTITY,
                'price_charged_per_table',
                [
                    'type' => 'int',
                    'group' => '',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Charged Per',
                    'input' => 'select',
                    'class' => '',
                    'source' => \Webkul\MpAdvancedBookingSystem\Model\Source\PriceChargedPerOptionsTable::class,
                    'default' => 1,
                    'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                    'visible' => false,
                    'required' => false,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
            $priceChargedPerTableAttrId = $eavSetup->getAttributeId(
                $entityTypeId,
                'price_charged_per_table'
            );
            $bookingAttributes['table'][] = $priceChargedPerTableAttrId;
            
            $eavSetup->addAttribute(
                Product::ENTITY,
                'max_capacity',
                [
                    'type' => 'varchar',
                    'group' => '',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'No. Of Guests Capacity',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => false,
                    'required' => true,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
            $maxCapacityAttrId = $eavSetup->getAttributeId(
                $entityTypeId,
                'max_capacity'
            );
            $bookingAttributes['table'][] = $maxCapacityAttrId;

            $eavSetup->addAttribute(
                Product::ENTITY,
                'no_of_guests',
                [
                    'type' => 'varchar',
                    'group' => '',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'No. Of Guests',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => false,
                    'default' => 1,
                    'required' => true,
                    'user_defined' => true,
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
            $noOfGuestsAttrId = $eavSetup->getAttributeId(
                $entityTypeId,
                'no_of_guests'
            );
            $bookingAttributes['table'][] = $noOfGuestsAttrId;

            foreach ($bookingAttributeSets as $bookingAttributeSetType => $bookingAttributeSetData) {
                $this->assignAttributesToAttibuteSet(
                    $bookingAttributes,
                    $bookingAttributeSetType,
                    $bookingAttributeSetData['id'],
                    $bookingAttributeSetData['group_id']
                );
            }

            $this->setPriceAttribute();
            $this->moduleDataSetup->getConnection()->endSetup();
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Setup_Patch_Data_ProductAttributes apply : ".$e->getMessage());
        }
    }
    
    /**
     * Create attribute set and return it
     *
     * @param string $attributeSetName
     *
     * @return \Magento\Eav\Model\Entity\Attribute\Set
     */
    public function createAttributeSet($attributeSetName)
    {
        $entityType = $this->eavConfig->getEntityType(Product::ENTITY);
        $entityTypeId = $entityType->getId();
        $defaultSetId = $entityType->getDefaultAttributeSetId();
        $attributeSet = $this->attributeSetFactory->create();
        $setCollection = $attributeSet->getResourceCollection()
            ->addFieldToFilter('entity_type_id', $entityTypeId)
            ->addFieldToFilter('attribute_set_name', $attributeSetName)
            ->load();
        $attributeSet = $setCollection->fetchItem();

        if (!$attributeSet) {
            $attributeSet = $this->attributeSetFactory->create();
            $attributeSet->setEntityTypeId($entityTypeId);
            $attributeSet->setAttributeSetName($attributeSetName);
            $attributeSet->save();
            $attributeSet->initFromSkeleton($defaultSetId);
            $attributeSet->save();
        }
        return $attributeSet;
    }
    
    /**
     * Assign booking attribute's to booking attribute set's
     *
     * @param array $bookingAttributes
     * @param string $bookingAttributeSetType
     * @param int $attributeSetId
     * @param int $attributeGroupId
     * @return void
     */
    public function assignAttributesToAttibuteSet(
        $bookingAttributes,
        $bookingAttributeSetType,
        $attributeSetId,
        $attributeGroupId
    ) {
        $attributeIds = array_merge($bookingAttributes[$bookingAttributeSetType], $bookingAttributes['all']);
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        
        $entityType = $this->eavConfig->getEntityType(Product::ENTITY);
        $entityTypeId = $entityType->getId();
        
        foreach ($attributeIds as $attributeId) {
            $eavSetup->addAttributeToSet(
                $entityTypeId,
                $attributeSetId,
                $attributeGroupId,
                $attributeId
            );
        }
    }
    
    /**
     * Assign price and tax class attribute's to booking attribute set's
     *
     * @param array $attributeIds
     * @param int $attributeSetId
     * @param int $attributeGroupId
     */
    public function setPriceAttribute()
    {
        try {
            $fieldList = ['price', 'tax_class_id'];
            $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
            foreach ($fieldList as $field) {
                $applyTo = explode(
                    ',',
                    $eavSetup->getAttribute(Product::ENTITY, $field, 'apply_to')
                );
                if (!in_array('booking', $applyTo)) {
                    $applyTo[] = 'booking';
                    $eavSetup->updateAttribute(
                        Product::ENTITY,
                        $field,
                        'apply_to',
                        implode(',', $applyTo)
                    );
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Setup_Patch_Data_ProductAttributes setPriceAttribute : ".$e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }
}
