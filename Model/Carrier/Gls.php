<?php

namespace ScaliaGroup\Integration\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Tracking\Result as TrackingResult;

class Gls extends AbstractCarrierOnline implements CarrierInterface
{
    public const CARRIER_CODE = 'gls';
    const TRACKING_URL_TEMPLATE = "https://www.gls-italy.com/?option=com_gls&view=track_e_trace&mode=search&numero_spedizione=%s&tipo_codice=nazionale";

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
        return [$this->_code => 'GLS'];
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
            'carrier_title' => 'GLS',
            'url' => sprintf(self::TRACKING_URL_TEMPLATE, $shipmentNumber)
        ];

        $status = $this->_trackStatusFactory->create(['data' => $statusData]);
        $result->append($status);

        return $result;
    }
}