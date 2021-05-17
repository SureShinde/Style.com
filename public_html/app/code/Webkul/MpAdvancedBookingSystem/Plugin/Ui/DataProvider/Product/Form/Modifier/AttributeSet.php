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
namespace Webkul\MpAdvancedBookingSystem\Plugin\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AttributeSet as ModifierAttributeSet;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;

class AttributeSet
{

    /**
     * Set collection factory
     *
     * @var CollectionFactory
     */
    protected $attributeSetCollection;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Catalog\Model\Locator\LocatorInterface
     */
    protected $locator;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
     */
    private $helperData;

    /**
     * @param \Magento\Catalog\Model\Locator\LocatorInterface $locator
     * @param CollectionFactory                               $attributeSetCollection
     * @param \Magento\Framework\UrlInterface                 $urlBuilder
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data               $helperData
     */
    public function __construct(
        \Magento\Catalog\Model\Locator\LocatorInterface $locator,
        CollectionFactory $attributeSetCollectionFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helperData
    ) {
        $this->locator = $locator;
        $this->attributeSetCollectionFactory = $attributeSetCollectionFactory;
        $this->urlBuilder = $urlBuilder;
        $this->helperData = $helperData;
    }

    public function afterModifyMeta(ModifierAttributeSet $subject, $result)
    {
        $typeId = $this->locator->getProduct()->getTypeId();
        if ($typeId == "booking" || $typeId == "hotelbooking") {
            $attributeSetIds = $this->helperData->getAllowedAttrSetIDsArray();
            $result['product-details']
                ['children']
                    ['attribute_set_id']
                        ['arguments']
                            ['data']
                                ['config']
                                    ['label'] = __('Booking Type');
                                    
            $result['product-details']
                ['children']
                    ['attribute_set_id']
                        ['arguments']
                            ['data']
                                ['config']
                                    ['options'] = $attributeSetIds;

        }
        return $result;
    }
}
