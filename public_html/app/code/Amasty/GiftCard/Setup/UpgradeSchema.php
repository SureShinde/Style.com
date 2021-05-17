<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Operation\CreateCodePoolTable
     */
    private $createCodePoolTable;

    /**
     * @var Operation\CreateCodeTable
     */
    private $createCodeTable;

    /**
     * @var Operation\CreateCodePoolRuleTable
     */
    private $createCodePoolRuleTable;

    /**
     * @var Operation\CreateImageTable
     */
    private $createImageTable;

    /**
     * @var Operation\CreateGiftCardPriceTable
     */
    private $createGiftCardPriceTable;

    /**
     * @var Operation\UpdateSchemaTo200
     */
    private $updateSchemaTo200;

    public function __construct(
        Operation\CreateCodePoolTable $createCodePoolTable,
        Operation\CreateCodeTable $createCodeTable,
        Operation\CreateCodePoolRuleTable $createCodePoolRuleTable,
        Operation\CreateImageTable $createImageTable,
        Operation\CreateGiftCardPriceTable $createGiftCardPriceTable,
        Operation\UpdateSchemaTo200 $updateSchemaTo200
    ) {
        $this->createCodePoolTable = $createCodePoolTable;
        $this->createCodeTable = $createCodeTable;
        $this->createCodePoolRuleTable = $createCodePoolRuleTable;
        $this->createImageTable = $createImageTable;
        $this->createGiftCardPriceTable = $createGiftCardPriceTable;
        $this->updateSchemaTo200 = $updateSchemaTo200;
    }

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if ($context->getVersion() && version_compare($context->getVersion(), '2.0.0', '<')) {
            $disabled = explode(',', str_replace(' ', ',', ini_get('disable_functions')));
            if (!in_array('class_exists', $disabled)
                && function_exists('class_exists')
                && class_exists(\Amasty\GiftCard\Cron\SendGiftCard::class)) {
                throw new \RuntimeException("This update requires removing folder app/code/Amasty/GiftCard\n"
                    . "Remove this folder and unpack new version of package into app/code/Amasty/\n"
                    . "Run `php bin/magento setup:upgrade` again\n");
            }
            $this->updateSchemaTo200->execute($setup);
        }

        $setup->endSetup();
    }
}
