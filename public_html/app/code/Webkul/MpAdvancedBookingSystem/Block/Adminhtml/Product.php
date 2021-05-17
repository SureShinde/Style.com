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
namespace Webkul\MpAdvancedBookingSystem\Block\Adminhtml;

use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\Catalog\Model\Product\TypeFactory;

class Product extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
     */
    protected $helper;

    /**
     * @var TypeFactory
     */
    protected $_typeFactory;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data     $helper
     * @param TypeFactory                           $typeFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper,
        TypeFactory $typeFactory,
        array $data = []
    ) {
        $this->_typeFactory = $typeFactory;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Prepare button and grid
     *
     * @return \Magento\Catalog\Block\Adminhtml\Product
     */
    protected function _prepareLayout()
    {
        $addButtonProps = [
            'id' => 'add_new_product',
            'label' => __('Add New Booking'),
            'class' => 'add',
            'button_class' => '',
            'class_name' => \Magento\Backend\Block\Widget\Button\SplitButton::class,
            'options' => $this->_getAddProductButtonOptions(),
        ];
        $this->buttonList->add('add_new', $addButtonProps);

        return parent::_prepareLayout();
    }

    /**
     * _getAddProductButtonOptions
     * Retrieve options for 'Add Product' split button
     *
     * @return array
     */
    protected function _getAddProductButtonOptions()
    {
        $splitButtonOptions = [];
        $types = $this->_typeFactory->create()->getTypes();
        uasort(
            $types,
            function ($elementOne, $elementTwo) {
                return ($elementOne['sort_order'] < $elementTwo['sort_order']) ? -1 : 1;
            }
        );
        if (!empty($types['booking'])) {
            $typeId = 'booking';
            $defaultSetId = $this->helper->getProductAttributeSetIdByLabel(
                'Default'
            );
            $hotelTypeSetId = $this->helper->getProductAttributeSetIdByLabel(
                'Hotel Booking'
            );
            $allBookingAttrSets = $this->helper->getAllowedAttrSetIDsArray();
            foreach ($allBookingAttrSets as $key => $attrSet) {
                $setId = $attrSet['value'];
                $setLabel = $attrSet['label'];
                if ($hotelTypeSetId == $setId) {
                    if (!empty($types['hotelbooking'])) {
                        $splitButtonOptions['booking_'.$setId] = [
                            'label' => __($setLabel),
                            'onclick' => "setLocation('" . $this->_getBookingCreateUrl('hotelbooking', $setId) . "')",
                            'default' => $defaultSetId == $setId,
                        ];
                    }
                } else {
                    $splitButtonOptions['booking_'.$setId] = [
                        'label' => __($setLabel),
                        'onclick' => "setLocation('" . $this->_getBookingCreateUrl($typeId, $setId) . "')",
                        'default' => $defaultSetId == $setId,
                    ];
                }
            }
        }

        return $splitButtonOptions;
    }

    /**
     * _getBookingCreateUrl
     * Retrieve booking create url by specified booking type
     *
     * @param string $type
     * @return string
     */
    protected function _getBookingCreateUrl($type, $setId)
    {
        return $this->getUrl(
            'catalog/product/new',
            ['set' => $setId, 'type' => $type]
        );
    }
}
