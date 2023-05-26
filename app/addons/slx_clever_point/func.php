<?php
/**
 * @copyright weblive.gr
 * @author Panos <panos@salix.gr>
 * Created: 01/Απρ/2023
 * Time: 15:59
 */

use Slx\CleverPoint\Api;
use Slx\CleverPoint\MyShipment;
use Slx\CleverPoint\SettingsHelpers;
use Slx\CleverPoint\ProfileHelpers;
use Tygh\Registry;

function fn_slx_clever_point_install() {
    $id = db_get_field("select service_id from ?:shipping_services where module=?s",'cleverpoint');
    if( !$id ) {
        $id = db_query("INSERT INTO ?:shipping_services (`service_id`, `status`, `module`, `code`, `sp_file`) VALUES (NULL, 'A', 'cleverpoint', 'cp', '');");
        db_query("INSERT INTO ?:shipping_service_descriptions (`service_id`, `description`, `lang_code`) VALUES (?i, 'Clever Point', 'en')", $id);
        db_query("INSERT INTO ?:shipping_service_descriptions (`service_id`, `description`, `lang_code`) VALUES (?i, 'Clever Point', 'el')", $id);
    }
}

function fn_settings_variants_addons_slx_clever_point_shipping_methods() {
    return SettingsHelpers::getShippingMethodsList();
}

function fn_settings_variants_addons_slx_clever_point_cp_status() {
    return SettingsHelpers::getOrderStatusesList();
}

function fn_settings_variants_addons_slx_clever_point_cod_payment_ids() {
    return SettingsHelpers::getPaymentMethodsList();
}

function slx_clever_point_settings_info_cu() {
    return sprintf("<p>%s</p>", __("slx_clever_point.settings_info_cu"));
}

function fn_settings_variants_addons_slx_clever_point_cu_firstname() {
    return ProfileHelpers::getCustomerProfileFieldList(false);
}

function fn_settings_variants_addons_slx_clever_point_cu_lastname() {
    return ProfileHelpers::getCustomerProfileFieldList(false);
}

function fn_settings_variants_addons_slx_clever_point_cu_address1() {
    return ProfileHelpers::getCustomerProfileFieldList(false);
}

function fn_settings_variants_addons_slx_clever_point_cu_address2() {
    return ProfileHelpers::getCustomerProfileFieldList(false);
}

function fn_settings_variants_addons_slx_clever_point_cu_city() {
    return ProfileHelpers::getCustomerProfileFieldList(false);
}

function fn_settings_variants_addons_slx_clever_point_cu_country() {
    return ProfileHelpers::getCustomerProfileFieldList(false);
}

function fn_settings_variants_addons_slx_clever_point_cu_zipcode() {
    return ProfileHelpers::getCustomerProfileFieldList(false);
}

function fn_settings_variants_addons_slx_clever_point_cu_tel() {
    return ProfileHelpers::getCustomerProfileFieldList(false);
}

function fn_settings_variants_addons_slx_clever_point_cu_mobile() {
    return ProfileHelpers::getCustomerProfileFieldList(false);
}

/*
 * Helper
 * Used also in design/themes/responsive/templates/addons/slx_clever_point/hooks/checkout/shipping_method.post.tpl
 */
function slx_clever_point_is_clever_point_shipping_method($id) {
    $out = false;
    $myMethods = Registry::get('addons.slx_clever_point.shipping_methods');
    if (is_array($myMethods)) {
        $shipping_ids = array_keys($myMethods);
        $out = in_array($id, $shipping_ids);
    }
    return $out;
}

/*
 * Helper
 * used in design/themes/responsive/templates/addons/slx_clever_point/hooks/checkout/shipping_method.post.tpl
 */
function slx_clever_point_is_customer_in_gr($cart) {
    $out = false;
    if(!empty($cart['user_data']) && !empty($cart['user_data']['s_country'])) {
        $out = $cart['user_data']['s_country']=='GR';
    }
    return $out;
}

/*
 * Helper
 * Used in design/themes/responsive/templates/addons/slx_clever_point/hooks/checkout/extra_shipping_cost.post.tpl
 * to show clever point extra cost.
 */
function slx_clever_point_get_cost_from_cart($cart) {
    $out = 0;
    foreach($cart['product_groups'] as $group) {
        if(!empty($group['clever_point_params']['cost'])) {
            $out += $group['clever_point_params']['cost'];
        }
    }
    return $out;
}

/*
 * Hook.
 * Get clever point cost from product group shipping and set it to cart['pickup_cost']
 *
 */
function fn_slx_clever_point_calculate_cart_content_after_shipping_calculation(&$cart,$auth,$calculate_shipping,$calculate_taxes,
                                                                               $options_style,$apply_cart_promotions,$lang_code,
                                                                               $area,$cart_products,&$product_groups) {
    $cart['pickup_cost'] = 0;
    if (empty(Tygh::$app['session']['clever_points'])) {
        Tygh::$app['session']['clever_points'] = [];
    }
    $clever_points = &Tygh::$app['session']['clever_points'];

    foreach($product_groups as $key_group => &$product_group) {
        if(isset($cart['chosen_shipping'][$key_group])) {
            $groupShippingId = $cart['chosen_shipping'][$key_group];
            if(isset($product_group['shippings'][$groupShippingId])) {
                if(slx_clever_point_is_clever_point_shipping_method($groupShippingId)) {
                    if(isset($clever_points[$key_group])) {
                        $product_groups[$key_group]['clever_point_params'] = $clever_points[$key_group];
                    }
                    $cart['pickup_cost'] = isset($product_groups[$key_group]['clever_point_params']['cost'])
                        ? $product_groups[$key_group]['clever_point_params']['cost']
                        : 0;
                }
                else {
                    unset($product_groups[$key_group]['clever_point_params']);

                        if(isset($clever_points[$key_group])) {
                            unset($clever_points[$key_group]);
                        }

                }
            }
        }
    }
}

/*
 * Hook
 * add to total cost clever point cost ($cart['pickup_cost']
 */
function fn_slx_clever_point_calculate_cart(&$cart, $cart_products, $auth, $calculate_shipping, $calculate_taxes, $apply_cart_promotions) {
    if(isset($cart['pickup_cost'])) {
        //$cart['total'] += $cart['pickup_cost'];
        $cart['display_shipping_cost'] = $cart['display_shipping_cost'] - $cart['pickup_cost'];
    }
    else {
        $cart['pickup_cost'] = 0;
    }
}

/*
 * Hook for versions prior 4.11.5
 * (starting from this version calculate_cart_content_after_shipping_calculation hook was added)
 *
 */
function fn_slx_clever_point_calculate_cart_post(&$cart, $auth, $calculate_shipping, $calculate_taxes, $options_style, $apply_cart_promotions, $cart_products, $product_groups) {
    $cart['pickup_cost'] = 0;
    if (empty(Tygh::$app['session']['clever_points'])) {
        Tygh::$app['session']['clever_points'] = [];
    }
    $clever_points = &Tygh::$app['session']['clever_points'];

    foreach($cart['product_groups'] as $key_group => $product_group) {
        if(isset($cart['chosen_shipping'][$key_group])) {
            $groupShippingId = $cart['chosen_shipping'][$key_group];
            if(isset($product_group['shippings'][$groupShippingId])) {
                if(slx_clever_point_is_clever_point_shipping_method($groupShippingId)) {
                    if(isset($clever_points[$key_group])) {
                        $cart['product_groups'][$key_group]['clever_point_params'] = $clever_points[$key_group];
                    }
                    $cart['pickup_cost'] = isset($cart['product_groups'][$key_group]['clever_point_params']['cost'])
                        ? $cart['product_groups'][$key_group]['clever_point_params']['cost']
                        : 0;
                }
                else {
                    unset($cart['product_groups'][$key_group]['clever_point_params']);
                    if(isset($clever_points[$key_group])) {
                        unset($clever_points[$key_group]);
                    }
                }
            }
        }
    }

    if(isset($cart['pickup_cost'])) {
        $cart['total'] += $cart['pickup_cost'];
        $cart['display_shipping_cost'] = $cart['display_shipping_cost'] - $cart['pickup_cost'];
    }
    else {
        $cart['pickup_cost'] = 0;
    }

}


/*
 * Helper.
 * Get pickup point info from cart's product group shipping method
 * Used in:
 * - design/themes/responsive/templates/addons/slx_clever_point/hooks/checkout/shipping_method.post.tpl
 */
function slx_clever_point_get_selected_point_from_cart($cart,$group_key) {
    $out = '';
    if(isset($cart['product_groups'][$group_key]['clever_point_params'])) {
        if(!empty($cart['product_groups'][$group_key]['clever_point_params']['point']['StationId'])) {
            $out = $cart['product_groups'][$group_key]['clever_point_params']['point'];
        }
    }
    return $out;
}

function fn_slx_clever_point_get_order_voucher($orderId) {
    $voucher = db_get_field("select distinct s.tracking_number from ?:shipments as s left join ?:shipment_items as si on (s.shipment_id=si.shipment_id) where si.order_id=?i", $orderId);
    return $voucher;
}

function fn_slx_clever_point_get_orders_from_vouchers($vouchers) {
    $orders = db_get_array("SELECT b.order_id, a.tracking_number from ?:shipments as a left join ?:shipment_items b on (a.shipment_id=b.shipment_id) WHERE tracking_number in (?a)", $vouchers);
    return $orders;
}

function slx_clever_point_get_clever_point_from_order($order_info) {
    $out = [];
    foreach($order_info['product_groups'] as $group) {
        if(!empty($group['clever_point_params']['point'])) {
            $out = $group['clever_point_params']['point'];
        }
    }
    return $out;
}

function slx_clever_point_can_create_for_orders($order_info) {
    $out = false;
    $clever_point =slx_clever_point_get_clever_point_from_order($order_info);
    if($clever_point) {
        if(!$order_info['clever_point_shipments']) {
            $cp_statuses = array_keys(Registry::get('addons.slx_clever_point.cp_status'));
            if(in_array($order_info['status'], $cp_statuses)) {
                $out = true;
            }
        }
    }
    return $out;
}

function fn_slx_clever_point_get_order_info(&$order, $additional_data) {
    $order['clever_point_shipments'] = [];
    $ocp = new \Slx\CleverPoint\OrderCPShipments();
    list($order['clever_point_shipments'],) = $ocp->findByOrderId($order['order_id']);
    if($order['clever_point_shipments']) {
        foreach($order['clever_point_shipments'] as &$shipment) {
            $shipment['response'] = json_decode($shipment['response'], true);
        }
    }
    $order['display_shipping_cost'] -= $order['pickup_cost'];
}

function slx_clever_point_get_cost() {
    $cost = Registry::ifGet('slx_clever_point_cost', -1);
    if($cost==-1) {
        $cApi = new Api();
        $costs = $cApi->getShippingGetPrices();
        if ($costs) {
            $cost = $costs['Pickup'];
            Registry::set('slx_clever_point_cost', $cost);
        }
    }
    return $cost;
}
function fn_slx_clever_point_shippings_get_shippings_list_post($group, $lang, $area, &$shippings_info) {
    foreach($shippings_info as &$item) {
        if(slx_clever_point_is_clever_point_shipping_method($item['shipping_id'])) {
            if(Registry::get('addons.slx_clever_point.cost_to')=='customer') {
                $cost = slx_clever_point_get_cost();
                $item['rate_info']['pickup_cost'] = $cost;
                $item['rate_info']['base_rate'] += $cost;
            }
        }
    }
}

function fn_slx_clever_point_form_cart($order_info, &$cart, $auth) {
    if (empty(Tygh::$app['session']['clever_points'])) {
        Tygh::$app['session']['clever_points'] = [];
    }
    $clever_points = &Tygh::$app['session']['clever_points'];
    foreach($order_info['product_groups'] as $group_key => $group) {
        $clever_points[$group_key] = $group['clever_point_params'];
    }
}