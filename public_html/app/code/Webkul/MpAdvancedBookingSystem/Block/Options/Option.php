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

namespace Webkul\MpAdvancedBookingSystem\Block\Options;

use Magento\Backend\Block\Widget;
use Magento\Catalog\Model\Product;

class Option extends Widget
{
    /**
     * @var Product
     */
    protected $_productInstance;

    /**
     * @var \Magento\Framework\DataObject[]
     */
    protected $_values;

    /**
     * @var string
     */
    protected $_template = 'Webkul_MpAdvancedBookingSystem::product/booking/event/option.phtml';

    /**
     * @var int
     */
    protected $_itemCount = 1;

    /**
     * @var \Magento\Catalog\Model\ProductOptions\ConfigInterface
     */
    protected $_productOptionConfig;

    /**
     * @var Product
     */
    protected $_product;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $_configYesNo;

    /**
     * @var \Magento\Catalog\Model\Config\Source\Product\Options\Type
     */
    protected $_optionType;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Config\Model\Config\Source\Yesno $configYesNo
     * @param \Magento\Catalog\Model\Config\Source\Product\Options\Type $optionType
     * @param Product $product
     * @param \Magento\Catalog\Model\ProductOptions\ConfigInterface $productOptionConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Backend\Block\Template\Context $context2,
        \Magento\Config\Model\Config\Source\Yesno $configYesNo,
        \Magento\Catalog\Model\Config\Source\Product\Options\Type $optionType,
        Product $product,
        \Magento\Catalog\Model\ProductOptions\ConfigInterface $productOptionConfig,
        array $data = []
    ) {
        $this->_optionType = $optionType;
        $this->_configYesNo = $configYesNo;
        $this->_product = $product;
        $this->_productOptionConfig = $productOptionConfig;
        parent::__construct($context2, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setCanReadPrice(true);
        $this->setCanEditPrice(true);
    }

    /**
     * getFieldName
     * Retrieve options field name prefix
     *
     * @return string
     */
    public function getFieldName()
    {
        return 'product[options]';
    }

    /**
     * getFieldId
     * Retrieve options field id prefix
     *
     * @return string
     */
    public function getFieldId()
    {
        return 'product_option';
    }

    /**
     * isReadonly
     * Check block is readonly
     *
     * @return bool
     */
    public function isReadonly()
    {
        return $this->getProduct()->getOptionsReadonly();
    }

    /**
     * getItemCount
     *
     * @return int
     */
    public function getItemCount()
    {
        return $this->_itemCount;
    }

    /**
     * setItemCount
     *
     * @param int $itemCount
     * @return $this
     */
    public function setItemCount($itemCount)
    {
        $this->_itemCount = max($this->_itemCount, $itemCount);
        return $this;
    }

    /**
     * getProduct
     * Get Product
     *
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->_productInstance) {
            $id = $this->getRequest()->getParam('id');
            $product = $this->_product->load($id);
            if ($product) {
                $this->setProduct($product);
            } else {
                $this->_productInstance = $this->_product;
            }
        }

        return $this->_productInstance;
    }

    /**
     * setProduct
     *
     * @param Product $product
     * @return $this
     */
    public function setProduct($product)
    {
        $this->_productInstance = $product;
        return $this;
    }

    /**
     * _prepareLayout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addChild('select_option_type', \Webkul\MpAdvancedBookingSystem\Block\Options\Type\Select::class);

        return parent::_prepareLayout();
    }

    /**
     * getAddButtonId
     *
     * @return mixed
     */
    public function getAddButtonId()
    {
        $id = $this->getLayout()->getBlock('customoption_option_add')->getChildBlock('add_button')->getId();
        return $id;
    }
    /**
     * getTemplatesHtml
     * Retrieve html templates for different types of product custom options
     *
     * @return string
     */
    public function getTemplatesHtml()
    {
        $canEditPrice = $this->getCanEditPrice();
        $canReadPrice = $this->getCanReadPrice();

        $this->getChildBlock('select_option_type')->setCanReadPrice($canReadPrice)->setCanEditPrice($canEditPrice);

        $template = $this->getChildHtml(
            'select_option_type'
        );

        return $template;
    }

    /**
     * getRequireSelectHtml
     *
     * @return mixed
     */
    public function getRequireSelectHtml()
    {
        $select = $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Html\Select::class
        )->setData(
            ['id' => $this->getFieldId() . '_<%- data.id %>_is_require', 'class' => 'select']
        )->setName(
            $this->getFieldName() . '[<%- data.id %>][is_require]'
        )->setOptions(
            $this->_configYesNo->toOptionArray()
        );

        return $select->getHtml();
    }
    
    /**
     * getTypeSelectHtml
     *
     * @return mixed
     */
    public function getTypeSelectHtml()
    {
        $optionTypes = $this->_optionType->toOptionArray();
        foreach ($optionTypes as $key => $opt) {
            if (!empty($opt['optgroup-name']) && $opt['optgroup-name'] == "Select") {
                // $key = array_search('multiple', array_column($opt['value'], 'value'));
                if (!empty($opt['value'])) {
                    foreach ($opt['value'] as $innerKey => $inner) {
                        if ($inner['value']!=="multiple") {
                            unset($optionTypes[$key]['value'][$innerKey]);
                        }
                    }
                }
            } else {
                unset($optionTypes[$key]);
            }
        }
        $select = $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Html\Select::class
        )->setData(
            [
                'id' => $this->getFieldId() . '_<%- data.id %>_type',
                'class' => 'select select-product-option-type required-option-select',
            ]
        )->setName(
            $this->getFieldName() . '[<%- data.id %>][type]'
        )->setOptions(
            $optionTypes
        );

        return $select->getHtml();
    }
    
    /**
     * getOptionValues
     *
     * @return \Magento\Framework\DataObject[]
     */
    public function getOptionValues()
    {
        $optionsArray = $this->getProduct()->getOptions();
        if ($optionsArray == null) {
            $optionsArray = [];
        }
        if (!$this->_values || $this->getIgnoreCaching()) {
            $showPrice = $this->getCanReadPrice();
            $values = [];
            $scope = (int)$this->_scopeConfig->getValue(
                \Magento\Store\Model\Store::XML_PATH_PRICE_SCOPE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            foreach ($optionsArray as $option) {
                if ($option->getType()=="multiple") {
                    /* @var $option \Magento\Catalog\Model\Product\Option */
                    $this->setItemCount($option->getOptionId());

                    $optionValue = [];

                    $optionValue['id'] = $option->getOptionId();
                    $optionValue['item_count'] = $this->getItemCount();
                    $optionValue['option_id'] = $option->getOptionId();
                    $optionValue['title'] = $option->getTitle();
                    $optionValue['type'] = $option->getType();
                    $optionValue['is_require'] = $option->getIsRequire();
                    $optionValue['sort_order'] = $option->getSortOrder();
                    $optionValue['can_edit_price'] = $this->getCanEditPrice();
                    if ($option->getPriceType() == '') {
                        $optionValue['price_type'] = 'fixed';
                    }
                    $optionValue['price'] = $showPrice ? $this->getPriceValue(
                        $option->getPrice(),
                        $option->getPriceType()
                    ) : '';
                    $optionValue['sku'] = $option->getSku();
                    $optionValue['max_characters'] = $option->getMaxCharacters();
                    $optionValue['file_extension'] = $option->getFileExtension();
                    $optionValue['image_size_x'] = $option->getImageSizeX();
                    $optionValue['image_size_y'] = $option->getImageSizeY();
                    
                    if ($option->getGroupByType() == \Magento\Catalog\Model\Product\Option::OPTION_GROUP_SELECT) {
                        $i = 0;
                        $itemCount = 0;
                        foreach ($option->getValues() as $_value) {
                            /* @var $_value \Magento\Catalog\Model\Product\Option\Value */
                            $optionValue['optionValues'][$i] = [
                                'item_count' => max($itemCount, $_value->getOptionTypeId()),
                                'option_id' => $_value->getOptionId(),
                                'option_type_id' => $_value->getOptionTypeId(),
                                'title' => $_value->getTitle(),
                                'price' => $showPrice ? $this->getPriceValue(
                                    $_value->getPrice(),
                                    $_value->getPriceType()
                                ) : '',
                                'price_type' => $showPrice ? $_value->getPriceType() : 0,
                                'sku' => $_value->getSku(),
                                'sort_order' => $_value->getSortOrder(),
                                'description' => $_value->getDescription(),
                                'is_in_stock' => $_value->getIsInStock(),
                                'qty' => $_value->getQty(),
                            ];
                            $i++;
                        }
                    } else {
                        $optionValue['price'] = $showPrice ? $this->getPriceValue(
                            $option->getPrice(),
                            $option->getPriceType()
                        ) : '';
                        $optionValue['price_type'] = $option->getPriceType();
                        $optionValue['sku'] = $option->getSku();
                        $optionValue['max_characters'] = $option->getMaxCharacters();
                        $optionValue['file_extension'] = $option->getFileExtension();
                        $optionValue['image_size_x'] = $option->getImageSizeX();
                        $optionValue['image_size_y'] = $option->getImageSizeY();
                        if ($this->getProduct()->getStoreId() != '0'
                            && $scope == \Magento\Store\Model\Store::PRICE_SCOPE_WEBSITE
                        ) {
                            $optionValue['checkboxScopePrice'] = $this->getCheckboxScopeHtml(
                                $option->getOptionId(),
                                'price',
                                ($option->getStorePrice()===null)
                            );
                            $optionValue['scopePriceDisabled'] = ($option->getStorePrice()===null) ? 'disabled' : null;
                        }
                    }
                    $values[] = new \Magento\Framework\DataObject($optionValue);
                }
            }
            $this->_values = $values;
        }
        return $this->_values;
    }
    /**
     * getPriceValue
     *
     * @param float $value
     * @param string $type
     * @return string
     */
    public function getPriceValue($value, $type)
    {
        if ($type == 'percent') {
            return number_format($value, 2, null, '');
        } elseif ($type == 'fixed') {
            return number_format($value, 2, null, '');
        }
    }

    /**
     * getCheckboxScopeHtml
     * Retrieve html of scope checkbox
     *
     * @param string $id
     * @param string $name
     * @param boolean $checked
     * @param string $select_id
     * @param array $containers
     * @return string
     */
    public function getCheckboxScopeHtml($id, $name, $checked = true, $selectId = '-1', array $containers = [])
    {
        $checkedHtml = '';
        if ($checked) {
            $checkedHtml = ' checked="checked"';
        }
        $selectHtmlName = '';
        $selectHtmlId = '';
        if ($selectId != '-1') {
            $selectHtmlName = '[values][' . $selectId . ']';
            $selectHtmlId = 'select_' . $selectId . '_';
        }
        $containers[] = '$(this).up(1)';
        $containers = implode(',', $containers);
        $localId = $this->getFieldId() . '_' . $id . '_' . $selectHtmlId . $name . '_use_default';
        $localName = "options_use_default[" . $id . "]" . $selectHtmlName . "[" . $name . "]";
        $useDefaultHtml =
            '<div class="field-service">'
            . '<input type="checkbox" class="use-default-control"'
            . ' name="' . $localName . '"' . 'id="' . $localId . '"'
            . ' value=""'
            . $checkedHtml
            . ' onchange="toggleSeveralValueElements(this, [' . $containers . ']);" '
            . ' />'
            . '<label for="' . $localId . '" class="use-default">'
            . '<span class="use-default-label">' . __('Use Default') . '</span></label></div>';

        return $useDefaultHtml;
    }

    /**
     * getCurrentProductId
     * Return current product id
     *
     * @return null|int
     */
    public function getCurrentProductId()
    {
        return $this->getProduct()->getId();
    }
}
