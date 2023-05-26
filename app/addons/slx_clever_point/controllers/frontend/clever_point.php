<?php
/**
 * @copyright weblive.gr
 * @author Panos <panos@salix.gr>
 * Created: 04/Απρ/2023
 * Time: 20:59
 */
if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

set_time_limit(0);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if($mode=='track') {
    $awb = $_REQUEST['awb'];
    $ocp = new \Slx\CleverPoint\OrderCPShipments();
    $shipment = $ocp->findOneByVoucher($awb);
    $tracking = [];
    $order_id = '';
    if($shipment) {
        $order_id = $shipment['order_id'];
        $api = new \Slx\CleverPoint\Api();
        $scans = $api->getTracking($awb);
    }
    else {
        fn_set_notification('W', __("error"), __("slx_clever_point.awb_not_found"));
    }
    $view = Tygh::$app['view'];
    $view->assign('scans', $scans);
    $view->assign('order_id', $order_id);
}

