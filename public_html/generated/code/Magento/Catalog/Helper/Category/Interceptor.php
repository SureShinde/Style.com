<?php
namespace Magento\Catalog\Helper\Category;

/**
 * Interceptor class for @see \Magento\Catalog\Helper\Category
 */
class Interceptor extends \Magento\Catalog\Helper\Category implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Helper\Context $context, \Magento\Catalog\Model\CategoryFactory $categoryFactory, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\Data\CollectionFactory $dataCollectionFactory, \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository)
    {
        $this->___init();
        parent::__construct($context, $categoryFactory, $storeManager, $dataCollectionFactory, $categoryRepository);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreCategories($sorted = false, $asCollection = false, $toLoad = true)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getStoreCategories');
        return $pluginInfo ? $this->___callPlugins('getStoreCategories', func_get_args(), $pluginInfo) : parent::getStoreCategories($sorted, $asCollection, $toLoad);
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryUrl($category)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getCategoryUrl');
        return $pluginInfo ? $this->___callPlugins('getCategoryUrl', func_get_args(), $pluginInfo) : parent::getCategoryUrl($category);
    }

    /**
     * {@inheritdoc}
     */
    public function canShow($category)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'canShow');
        return $pluginInfo ? $this->___callPlugins('canShow', func_get_args(), $pluginInfo) : parent::canShow($category);
    }

    /**
     * {@inheritdoc}
     */
    public function canUseCanonicalTag($store = null)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'canUseCanonicalTag');
        return $pluginInfo ? $this->___callPlugins('canUseCanonicalTag', func_get_args(), $pluginInfo) : parent::canUseCanonicalTag($store);
    }

    /**
     * {@inheritdoc}
     */
    public function isModuleOutputEnabled($moduleName = null)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'isModuleOutputEnabled');
        return $pluginInfo ? $this->___callPlugins('isModuleOutputEnabled', func_get_args(), $pluginInfo) : parent::isModuleOutputEnabled($moduleName);
    }
}
