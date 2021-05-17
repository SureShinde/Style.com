<?php
namespace Klarna\Core\Model\Checkout\Orderline\Giftcard;

/**
 * Interceptor class for @see \Klarna\Core\Model\Checkout\Orderline\Giftcard
 */
class Interceptor extends \Klarna\Core\Model\Checkout\Orderline\Giftcard implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Klarna\Core\Helper\DataConverter $helper, \Magento\Tax\Model\Calculation $calculator, \Magento\Framework\App\Config\ScopeConfigInterface $config, \Magento\Framework\DataObjectFactory $dataObjectFactory, \Klarna\Core\Helper\KlarnaConfig $klarnaConfig)
    {
        $this->___init();
        parent::__construct($helper, $calculator, $config, $dataObjectFactory, $klarnaConfig);
    }

    /**
     * {@inheritdoc}
     */
    public function collect(\Klarna\Core\Api\BuilderInterface $checkout)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'collect');
        return $pluginInfo ? $this->___callPlugins('collect', func_get_args(), $pluginInfo) : parent::collect($checkout);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(\Klarna\Core\Api\BuilderInterface $checkout)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'fetch');
        return $pluginInfo ? $this->___callPlugins('fetch', func_get_args(), $pluginInfo) : parent::fetch($checkout);
    }

    /**
     * {@inheritdoc}
     */
    public function isIsTotalCollector()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'isIsTotalCollector');
        return $pluginInfo ? $this->___callPlugins('isIsTotalCollector', func_get_args(), $pluginInfo) : parent::isIsTotalCollector();
    }

    /**
     * {@inheritdoc}
     */
    public function getCode()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getCode');
        return $pluginInfo ? $this->___callPlugins('getCode', func_get_args(), $pluginInfo) : parent::getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'setCode');
        return $pluginInfo ? $this->___callPlugins('setCode', func_get_args(), $pluginInfo) : parent::setCode($code);
    }

    /**
     * {@inheritdoc}
     */
    public function getDiscountTaxAmount($items, $total, $taxRate)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getDiscountTaxAmount');
        return $pluginInfo ? $this->___callPlugins('getDiscountTaxAmount', func_get_args(), $pluginInfo) : parent::getDiscountTaxAmount($items, $total, $taxRate);
    }

    /**
     * {@inheritdoc}
     */
    public function getDiscountTaxRate($checkout, $items = [])
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getDiscountTaxRate');
        return $pluginInfo ? $this->___callPlugins('getDiscountTaxRate', func_get_args(), $pluginInfo) : parent::getDiscountTaxRate($checkout, $items);
    }
}
