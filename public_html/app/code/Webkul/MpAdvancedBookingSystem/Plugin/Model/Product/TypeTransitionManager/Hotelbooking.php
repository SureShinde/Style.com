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
namespace Webkul\MpAdvancedBookingSystem\Plugin\Model\Product\TypeTransitionManager;

use Closure;
use Magento\Framework\App\RequestInterface;

class Hotelbooking
{
    /**
     * Request instance
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Change product type to configurable if needed
     *
     * @param \Magento\Catalog\Model\Product\TypeTransitionManager $subject
     * @param Closure                                              $proceed
     * @param \Magento\Catalog\Model\Product                       $product
     * @return void
     */
    public function aroundProcessProduct(
        \Magento\Catalog\Model\Product\TypeTransitionManager $subject,
        Closure $proceed,
        \Magento\Catalog\Model\Product $product
    ) {
        $productType = $this->request->getParam('type');
        if ($productType=="hotelbooking") {
            $product->setTypeId(
                \Webkul\MpAdvancedBookingSystem\Model\Product\Type\Hotelbooking::TYPE_CODE
            );
            return;
        }
        $proceed($product);
    }
}
