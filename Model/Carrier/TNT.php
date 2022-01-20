<?php

namespace ScaliaGroup\Integration\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Tracking\Result as TrackingResult;

class TNT extends AbstractCarrierOnline implements CarrierInterface
{
    public const CARRIER_CODE = 'tnt';
    const TRACKING_URL_TEMPLATE = "https://www.tnt.it/tracking/getTrack.html?wt=1&consigNos=%s";

    protected $_code = self::CARRIER_CODE;

    protected $_isFixed = true;

    public function collectRates(RateRequest $request)
    {
        return false;
    }

    protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
        return false;
    }

    public function getAllowedMethods()
    {
        return [$this->_code => 'TNT'];
    }

    public function isTrackingAvailable()
    {
        return true;
    }

    public function getTracking(string $shipmentNumber) : TrackingResult
    {
        $result = $this->_trackFactory->create();

        $statusData = [
            'tracking' => $shipmentNumber,
            'carrier_title' => 'TNT',
            'url' => sprintf(self::TRACKING_URL_TEMPLATE, $shipmentNumber)
        ];

        $status = $this->_trackStatusFactory->create(['data' => $statusData]);
        $result->append($status);

        return $result;
    }
}