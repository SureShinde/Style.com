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

namespace Webkul\MpAdvancedBookingSystem\Block;

/*
 * Webkul MpAdvancedBookingSystem BookingProduct Block
 */
class BookingProduct extends \Webkul\Marketplace\Block\Product\Create
{
    /**
     * @return Widget
     */
    protected function _prepareLayout()
    {
        $this->addChild('options_box', \Webkul\MpAdvancedBookingSystem\Block\Options\Option::class);

        $this->addChild(
            'import_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Import Options'),
                'class' => 'add',
                'id' => 'import_new_defined_option'
            ]
        );

        return parent::_prepareLayout();
    }

    /**
     * getAddButtonHtml
     *
     * @return string
     */
    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }

    /**
     * getOptionsBoxHtml
     *
     * @return string
     */
    public function getOptionsBoxHtml()
    {
        return $this->getChildHtml('options_box');
    }
}
