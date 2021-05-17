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
namespace Webkul\MpAdvancedBookingSystem\Model;

use Webkul\MpAdvancedBookingSystem\Api\Data\QuestionInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Question extends \Magento\Framework\Model\AbstractModel implements QuestionInterface, IdentityInterface
{
    /**
     * No route page id.
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * MpAdvancedBookingSystem Question cache tag.
     */
    const CACHE_TAG = 'mphotelbooking_question';

    /**
     * @var string
     */
    protected $_cacheTag = 'mphotelbooking_question';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'mphotelbooking_question';

    /**
     * Pending question status code
     */
    const STATUS_PENDING = 0;

    /**
     * Approved question status code
     */
    const STATUS_APPROVED = 1;

    /**
     * Not Approved question status code
     */
    const STATUS_NOT_APPROVED = 2;

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Question::class);
    }

    /**
     * Load object data.
     *
     * @param int|null $id
     * @param string   $field
     *
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteItems();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route Items.
     *
     * @return \Webkul\MpAdvancedBookingSystem\Model\Question
     */
    public function noRouteItems()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Get identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\MpAdvancedBookingSystem\Api\Data\QuestionInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
