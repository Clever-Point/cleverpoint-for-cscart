<?php
/**
 * @copyright weblive.gr
 * @author Panos <panos@salix.gr>
 * Created: 04/Απρ/2023
 * Time: 18:30
 */

use Slx\CleverPoint\Api;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

set_time_limit(0);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == "generate") {
        $order_id = $_REQUEST['order_id'];
        $package = $_REQUEST['package'];
        $order_info = fn_get_order_info($order_id);
        if ($order_info) {
            $conv = new \Slx\CleverPoint\ConvertOrderToCpShipping();
            $so = $conv->convert($order_info, $package);
            $api = new \Slx\CleverPoint\Api();
            $resp = $api->postShipping($so);
            if ($resp) {
                $ocp = new \Slx\CleverPoint\OrderCPShipments();
                $ocp->update(0, [
                    'order_id' => $order_id,
                    'voucher' => $resp['ShipmentAwb'],
                    'response' => json_encode($resp),
                ]);
            } else {
                foreach ($api->messages as $message) {
                    fn_set_notification("E", 'Clever Point', $message['Code']);
                }
            }
        }
        $suffix = ".details?order_id=" . $_REQUEST['order_id'];
        return array(CONTROLLER_STATUS_REDIRECT, "orders" . $suffix);
    }
    if ($mode == 'voucher') {
        $awb = $_REQUEST['awb'];
        $api = new Api();
        $pdf = $api->getVouchers($awb);
        if ($pdf) {
            header(fn_get_content_disposition_header(sprintf("voucher-%s.pdf",$awb)));
            header("content-type: application/pdf");
            echo $pdf;
            die();
        } else {
            foreach ($api->messages as $message) {
                fn_set_notification("E", 'Clever Point', $message['Code']);
            }
        }
        $suffix = ".details?order_id=" . $_REQUEST['order_id'];
        return array(CONTROLLER_STATUS_REDIRECT, "orders" . $suffix);
    }

    if($mode=='cancel') {
        $awb = $_REQUEST['awb'];
        $order_id = $_REQUEST['order_id'];
        $api = new Api();
        $response = $api->postShipmentCancel($awb);
        if ($response) {
            $ocp = new \Slx\CleverPoint\OrderCPShipments();
            $ocp->deleteOrderShipment($order_id, $awb);
        } else {
            foreach ($api->messages as $message) {
                fn_set_notification("E", 'Clever Point', $message['Code']);
            }
        }
        $suffix = ".details?order_id=" . $_REQUEST['order_id'];
        return array(CONTROLLER_STATUS_REDIRECT, "orders" . $suffix);
    }

    if ($mode == 'manifest') {
        $dt = new \DateTime();
        $date =  $dt->format("Y-m-d");
        $api = new Api();
        $pdf = $api->getManifests($date);
        if ($pdf) {
            header(fn_get_content_disposition_header(sprintf("manifest-%s.pdf",$date)));
            header("content-type: application/pdf");
            echo $pdf;
            die();
        } else {
            foreach ($api->messages as $message) {
                fn_set_notification("E", 'Clever Point', $message['Code']);
            }
        }
        $suffix = ".manage";
        return array(CONTROLLER_STATUS_REDIRECT, "orders" . $suffix);
    }
}

if ($mode == 'step1') {
    $order_id = $_REQUEST['order_id'];

    $cpApi = new Api();
    $couriers = $cpApi->getShippingGetCarriers();
    $order_info = fn_get_order_info($order_id);
    $clever_point = slx_clever_point_get_clever_point_from_order($order_info);
    $view = Tygh::$app['view'];
    $view->assign('order_id', $order_id);
    $view->assign('mode', $mode);
    $view->assign('order_info', $order_info);
    $view->assign('couriers', $couriers);
    $view->assign('clever_point', $clever_point);
}

