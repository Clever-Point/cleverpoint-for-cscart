<?php
/**
 * @copyright weblive.gr
 * @author Panos <panos@salix.gr>
 * Created: 02/Απρ/2023
 * Time: 21:08
 */

namespace Slx\CleverPoint;

class SettingsHelpers {

    public static function getOrderStatusesList() {
        $statuses = fn_get_statuses();
        $data = array();
        foreach ($statuses as $k => $status) {
            $data[$k] = $status['description'];
        }
        return $data;
    }

    public static function getPaymentMethodsList() {
        $payments = fn_get_payments(['status'=>'A']);
        $data = [];
        foreach ($payments as $payment) {
            $data[$payment['payment_id']]=$payment['payment'];
        }
        return $data;
    }

    public static function getShippingMethodsList() {
        return fn_get_shippings(true);
    }
}