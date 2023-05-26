<?php
/**
 * @copyright weblive.gr
 * @author Panos <panos@salix.gr>
 * Created: 01/Απρ/2023
 * Time: 17:02
 */

namespace Slx\CleverPoint;

class Api {

    private $apiCall;
    public $messages = [];

    public function __construct() {
        $this->apiCall = new ApiCall();
    }

    /*
     * Get service fee.
     */
    public function getShippingGetPrices() {
        $response = $this->apiCall->get('Shipping/GetPrices');
        $out = [];
        if ($response) {
            if ($response['ResultType'] == 'Success') {
                $out = [];
                foreach ($response['Content'] as $item) {
                    $out[$item['Type']] = floatval($item['Price']['Value']);
                }
            }
        }
        return $out;
    }

    public function getShippingGetCarriers() {
        $response = $this->apiCall->get('Shipping/GetCarriers');
        $out = [];
        if ($response) {
            if ($response['ResultType'] == 'Success')
                foreach ($response['Content'] as $item) {
                    $out[$item['Id']] = $item;
                }
        }
        return $out;
    }

    public function postShipping($payload) {
        $out = [];
        $this->messages = [];
        $response = $this->apiCall->post('Shipping', [], $payload);
        //$response = $this->getFakeShipment();
        if ($response) {
            if ($response['ResultType'] == "Success") {
                $out = $response['Content'];
            } else {
                $out = false;
                $this->messages = $response['Messages'];
            }
        }
        return $out;
    }

    public function getVouchers($awbs) {
        $out = '';
        $qryParams = [
            'awbs' => is_array($awbs) ? implode(',', $awbs) : $awbs,
        ];
        $response = $this->apiCall->get('Vouchers', $qryParams);
        if ($response) {
            if ($response['ResultType'] == "Success") {
                $out = $response['Content']['Document'];
                $out = base64_decode($out);
            }
            else {
                $out = false;
                $this->messages = $response['Messages'];
            }
        }
        return $out;
    }

    public function postShipmentCancel($awbs) {
        $uri = sprintf(
            'Shipping/%s/Cancel',
            is_array($awbs) ? implode(',', $awbs) : $awbs
        );
        $out = '';
        $response = $this->apiCall->post($uri, [], []);
        if ($response) {
            if ($response['ResultType'] == "Success") {
                $out = $response['Content'];
            } else {
                $out = false;
                $this->messages = $response['Messages'];
            }
        }
        return $out;
    }

    public function getTracking($awb){
        //return $this->getFakeTracking();
        $out = [];
        $uri = sprintf("ShipmentTracking/%s", $awb);
        $response = $this->apiCall->get($uri, []);
        if ($response) {
            if ($response['ResultType'] == "Success") {
                $out = $response['Content'];
            } else {
                $out = false;
                $this->messages = $response['Messages'];
            }
        }
        return $out;
    }

    public function getManifests($date) {
        $out = '';
        $uri = sprintf("Manifests/%s", $date);
        $response = $this->apiCall->get($uri);
        if ($response) {
            if ($response['ResultType'] == "Success") {
                $out = $response['Content']['Document'];
                $out = base64_decode($out);
            }
            else {
                $out = false;
                $this->messages = $response['Messages'];
                if($response['ResultType']=='SuccessWithWarnings') {
                    $this->messages[] = ['Code' => $response['Content']['Description']];
                }
            }
        }
        return $out;
    }

    private function getFakeShipment() {
        $out = json_decode(
            '{
              "ResultType": "Success",
              "Content":
              {
                "ShipmentMasterId": "00001e92-d71e-e411-941e-000c29840282",
                "ShipmentAwb": "042000004321",
                "ItemCodes": "",
                "ShipmentStatus": "ConfirmedOrder",
                "ShipmentStatusDt": "2015-02-22T10:32:44",
                "ShipmentCost": { "CurrencyCode": "EUR", "Value": 3.0 }
              }
            }
            ',
            true
        );
        $out['Content']['ShipmentAwb'] = mt_rand(10000000000, 99999999999999);
        return $out;
    }

    private function getFakeTracking() {
        $response = array (
            'MainData' =>
                array (
                    'ShipmentDate' => '2015-11-30T00:00:00+02:00',
                    'ShipmentAwb' => '042000004431',
                    'ShipmentInboundAwb' => NULL,
                    'ShipmentOutboundAwb' => '042000004431',
                    'References' => ',51484564 ,042000004431,',
                    'ShipmentStatus' => 29,
                    'IsPickupAssignment' => false,
                    'ShipperName' => 'Z-MALL.GR',
                    'ShipperAttention' => '',
                    'ReceiptStationId' => NULL,
                    'ReceiptStationPrefix' => NULL,
                    'ReceiptStationCode' => NULL,
                    'ReceiptStationName' => NULL,
                    'ConsigneeName' => 'ΣΤΑΜ***** ΝΙΚΟ***** ',
                    'ConsigneeAttention' => 'ΣΤΑΜ***** ΝΙΚΟ***** ',
                    'DeliveryStationId' => '6172d896-057d-4bae-92a0-c0e32c5f20a9',
                    'DeliveryStationPrefix' => '5006',
                    'DeliveryStationCode' => 'EMA',
                    'DeliveryStationName' => 'EKO SERVICE ΑΘΑΝΑΣΙΟΥ',
                    'EshopName' => 'Z-MALL.GR',
                    'ShipmentStatusName' => 'DeliveryCompleted',
                ),
            'PODData' =>
                array (
                    'DeliveredTo' => 'ΣΤΑΜ***** ΝΙΚΙ***** ',
                    'DeliveredToIdentification' => '300877',
                    'DeliveredToIdentificationType' => '0',
                    'DeliveryDateTime' => '2015-12-01T17:56:22.018725+02:00',
                    'Comments' => '',
                    'StationName' => 'EKO SERVICE ΑΘΑΝΑΣΙΟΥ',
                ),
            'TrackingData' =>
                array (
                ),
        );
        $response['TrackingData'] = json_decode('[
     {
       "StationName": "ΦΑΡΜΑΚΕΙΟ ΜΑΛΑΦΕΚΑ - ΦΙΛΛΙΠΑΚΗ",
       "Timestamp": "2015-02-24T13:50:39.539136",
       "TrackingNote": "Η αποστολή 042000004761 παραδόθηκε επιτυχώς"
     },
     {
       "StationName": "ΦΑΡΜΑΚΕΙΟ ΜΑΛΑΦΕΚΑ - ΦΙΛΛΙΠΑΚΗ",
       "Timestamp": "2015-02-24T13:35:17.804784",
       "TrackingNote": "Όλα τα τεμάχια της αποστολής παραλήφθηκαν"
     },
     {
       "StationName": "CLEVER MAIN HUB",
       "Timestamp": "2015-02-24T12:34:31.820427",
       "TrackingNote": "Η ΑΠΟΣΤΟΛΗ ΔΗΜΙΟΥΡΓΗΘΗΚΕ"
     }
   ]', true);
        return $response;
    }
}