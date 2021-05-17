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
namespace Webkul\MpAdvancedBookingSystem\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Listing Columns Type
 */
class Type extends Column
{
    /**
     * {@inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        $dataSource = parent::prepareDataSource($dataSource);

        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            $item['type'] = $this->getTypeLabel($item);
        }

        return $dataSource;
    }

    /**
     * Retrieve type label
     *
     * @param  array $item
     * @return \Magento\Framework\Phrase
     */
    protected function getTypeLabel(array $item)
    {
        if (!empty($item['customer_id'])) {
            return __('Customer');
        }
        return __('Guest');
    }
}
