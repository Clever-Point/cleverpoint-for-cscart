<?php
/**
 * @copyright weblive.gr
 * @author Panos <panos@salix.gr>
 * Created: 04/Απρ/2023
 * Time: 19:10
 */

namespace Slx\CleverPoint;

use Tygh\Registry;

class ConvertOrderToCpShipping {
    private $cp;
    private $order_info;
    private $package_params;

    public function convert($orderInfo, $package) {
        $this->order_info = $orderInfo;
        $this->package_params = $package;
        $this->cp = $this->getBlank();
        $this->addItems();
        $this->addConsignee();
        $this->addDeliveryStation();
        $this->addCod();
        $this->addRest();
        return $this->cp;
    }

    private function getBlank() {
        return [
            'ItemsDescription' => '',
            'Consignee' => [
                "ContactName" => '',
                "Address" => '',
                "Area" => '',
                "City" => '',
                "PostalCode" => '',
                "Country" => '',
                "Phones" => '',
                "NotificationPhone" => '',
                "Emails" => '',
                "CustomerReferenceId" => '',
            ],
            'DeliveryStation' => '',
            'Items' => [],
        ];
    }

    private function addItems() {
        $weight_per_parcel=round($this->package_params['weight']/$this->package_params['num_of_parcels'],2);
        for($i=1; $i<=$this->package_params['num_of_parcels']; $i++) {
            $this->cp['Items'][] = [
                'Description' => sprintf("%s %s %s/%s", __("order"), $this->order_info['order_id'], $i, $this->package_params['num_of_parcels']),
                'IsFragile' => false,
                'Weight' => [
                    'UnitType' => 'kg',
                    'Value' => $weight_per_parcel>0 ? $weight_per_parcel : 0.5,
                ],
            ];
        }
    }

    private function addConsignee() {
        $this->cp['Consignee']['ContactName'] =
            ProfileHelpers::getProfileFieldValue($this->order_info, Registry::ifGet("addons.slx_clever_point.cu_firstname",''), true)
            . ' '.
            ProfileHelpers::getProfileFieldValue($this->order_info, Registry::ifGet("addons.slx_clever_point.cu_lastname",''), true);
        $this->cp['Consignee']['Address'] = ProfileHelpers::getProfileFieldValue($this->order_info, Registry::ifGet("addons.slx_clever_point.cu_address1",''), false);
        $this->cp['Consignee']['Area'] = ProfileHelpers::getProfileFieldValue($this->order_info, Registry::ifGet("addons.slx_clever_point.cu_address2",''), false);
        $this->cp['Consignee']['City'] = ProfileHelpers::getProfileFieldValue($this->order_info, Registry::ifGet("addons.slx_clever_point.cu_city",''), false);
        $this->cp['Consignee']['PostalCode'] = ProfileHelpers::getProfileFieldValue($this->order_info, Registry::ifGet("addons.slx_clever_point.cu_zip",''), false);
        $this->cp['Consignee']['Country'] = ProfileHelpers::getProfileFieldValue($this->order_info, Registry::ifGet("addons.slx_clever_point.cu_country",''), false);
        $this->cp['Consignee']['Phones'] = ProfileHelpers::getProfileFieldValue($this->order_info, Registry::ifGet("addons.slx_clever_point.cu_tel",''), false);
        $this->cp['Consignee']['Phones'] = preg_replace("/[^0-9]/", "", $this->cp['Consignee']['Phones']);
        $this->cp['Consignee']['NotificationPhone'] = ProfileHelpers::getProfileFieldValue($this->order_info, Registry::ifGet("addons.slx_clever_point.cu_tel",''), false);
        $this->cp['Consignee']['NotificationPhone'] = preg_replace("/[^0-9]/", "", $this->cp['Consignee']['Phones']);
        $this->cp['Consignee']['Emails'] = $this->order_info['email'];
        $this->cp['Consignee']['CustomerReferenceId'] = $this->order_info['order_id'];
    }

    private function addDeliveryStation() {
        $point = slx_clever_point_get_clever_point_from_order($this->order_info);
        if($point) {
            $this->cp['DeliveryStation'] = $point['StationId'];
        }
    }

    private function addCod() {
        $codAmount = floatval($this->package_params['cod_amount']);
        if($codAmount!=0) {
            $this->cp['CODs'] = [];
            $this->cp['CODs'][] = [
                'Amount' => [
                    'CurrencyCode' => 'EUR',
                    'Value' => $codAmount,
                ],
            ];
        }
    }

    private function addRest() {
/*        $this->cp['References'] = [];
        if(!empty($this->package_params['courier_voucher'])) {
            $this->cp['References'][] = $this->package_params['courier_voucher'];
        }
        $this->cp['References'][] = $this->order_info['order_id'];*/
        $this->cp['ItemsDescription'] = __("order").' '. $this->order_info['order_id'];
        $this->cp['DeliveryInstruction'] = $this->order_info['notes'];

        $api = new Api();
        $couriers = $api->getShippingGetCarriers();
        $this->cp['ExternalCarrierId'] = $this->package_params['courier'];
        $this->cp['ExternalCarrierName'] = isset($couriers[$this->package_params['courier']]) ? $couriers[$this->package_params['courier']]['Name'] : '';
        $this->cp['ShipmentAwb']=$this->package_params['courier_voucher'];
    }
}
/*
{
  "ItemsDescription": "iPhone 6 16GB",
  "Consignee": {
    "ContactName": "Δημήτρης Ιωάννου",
    "Address": "Ελ. Βενιζέλου 52",
    "Area": "Νέο Φάληρο",
    "City": "Πειραιάς",
    "PostalCode": "12233",
    "Country": "GR",
    "Phones": "2104888734,6978557788",
    "NotificationPhone": "6978555788",
    "Emails": "dioannou@mysite.com",
    "Reference": "123456"
  },
  "DeliveryStation": "0dd74f1b-bfe9-4616-869d-c20859dbebcd",
  "References": [ "AH4564655", "7654344" ],
  "CODs": [
    {
      "Amount": { "CurrencyCode": "EUR", "Value": 600.0 }
    }
  ],
  "Items": [
    {
      "Description": "iPhone 6 16GB",
      "IsFragile": true,
      "Weight": { "UnitType": "kg", "Value": 0.7 }
    }
  ]
}
 */