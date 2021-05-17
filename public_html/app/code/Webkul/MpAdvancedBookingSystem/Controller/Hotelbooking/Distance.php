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
namespace Webkul\MpAdvancedBookingSystem\Controller\Hotelbooking;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Distance extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    private $curl;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context,
        \Magento\Framework\HTTP\Client\Curl $curl
    ) {
        parent::__construct($context);
        $this->curl = $curl;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        try {
            $url = "https://maps.googleapis.com/maps/api/distancematrix/json"
                    . "?origins=" . $data['origins']
                    . "&destinations=" . $data['destinations']
                    . "&key=" . $data['key'];
            $this->curl->get($url);
            $response = $this->curl->getBody();
            $responseArray = json_decode($response, true);
            if (!empty($responseArray)
                && isset($responseArray['status'])
                && isset($responseArray['rows'][0]['elements'])
                && $responseArray['status'] == "OK"
                && count($responseArray['rows'][0]['elements']) > 0
            ) {
                $distanceTime = [];
                foreach ($responseArray['rows'][0]['elements'] as $key => $element) {
                    if ($element['status'] == "OK") {
                        $distanceTime[$key] = [
                            'distance' => $element['distance']['text'],
                            'duration' => $element['duration']['text'],
                            'name' => $data['places'][$key]
                        ];
                    }
                }
                $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
                $resultJson->setData($distanceTime);
                return $resultJson;
            }
        } catch (\Exception $e) {
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData([]);
            return $resultJson;
        }
    }
}
