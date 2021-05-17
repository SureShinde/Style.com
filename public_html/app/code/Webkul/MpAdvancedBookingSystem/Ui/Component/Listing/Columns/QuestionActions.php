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
use Webkul\MpAdvancedBookingSystem\Model\Question;

/**
 * Listing Columns QuestionActions
 */
class QuestionActions extends Column
{
    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        $dataSource = parent::prepareDataSource($dataSource);

        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            if (isset($item['entity_id'])) {
                $item[$this->getData('name')] = [
                    'edit' => [
                        'href' => $this->context->getUrl(
                            'mpadvancebooking/hotelbooking/updatestatus',
                            [
                                'id' => $item['entity_id'],
                                'status' => Question::STATUS_APPROVED
                            ]
                        ),
                        'label' => __('Enable'),
                        'confirm' => [
                            'title' => __('Enable'),
                            'message' => __('Are you sure you want to Enable this query?')
                        ]
                    ],
                    'delete' => [
                        'href' => $this->context->getUrl(
                            'mpadvancebooking/hotelbooking/updatestatus',
                            [
                                'id' => $item['entity_id'],
                                'status' => Question::STATUS_NOT_APPROVED
                            ]
                        ),
                        'label' => __('Disable'),
                        'confirm' => [
                            'title' => __('Disable'),
                            'message' => __('Are you sure you want to Disable this query?')
                        ]
                    ]
                ];
            }
        }

        return $dataSource;
    }
}
