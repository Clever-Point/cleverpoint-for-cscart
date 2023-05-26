<?php
/**
 * @copyright weblive.gr
 * @author Panos <panos@salix.gr>
 * Created: 01/Απρ/2023
 * Time: 17:27
 */

use Slx\CleverPoint\Api;
use Tygh\Registry;

defined('BOOTSTRAP') or die('Access denied');

/** @var string $controller */
/** @var string $mode */
/** @var array $auth */

fn_enable_checkout_mode();

fn_define('ORDERS_TIMEOUT', 60);

// Cart is empty, create it
if (empty(Tygh::$app['session']['cart'])) {
    fn_clear_cart(Tygh::$app['session']['cart']);
}

/** @var array $cart */
$cart = &Tygh::$app['session']['cart'];

/** @var \Tygh\SmartyEngine\Core $view */
$view = Tygh::$app['view'];

if (empty(Tygh::$app['session']['clever_points'])) {
    Tygh::$app['session']['clever_points'] = [];
}
$clever_points = &Tygh::$app['session']['clever_points'];
foreach($clever_points as $group => $clever_point) {
    if(isset($cart['product_groups'][$group])) {
        $cart['product_groups'][$group]['clever_point_params'] = $clever_point;
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    /*
     * AJAX called when pickup point is selected.
     */
    if($mode=='clever_point_options') {
        $group_key = $_REQUEST['group_key'];
        if(!empty($cart['product_groups'][$group_key])) {
            $cart['product_groups'][$group_key]['clever_point_params'] = [
                'cost' => 0,
                'point' => $_REQUEST['point'],
            ] ;
            $cart['product_groups'][$group_key]['clever_point_params']['point']['IsOperationalForCOD']= ($cart['product_groups'][$group_key]['clever_point_params']['point']['IsOperationalForCOD']=='true');
            //$cart['product_groups'][$group_key]['clever_point_params']['point']['IsOperationalForCOD']=false;

            if(!empty($cart['product_groups'][$group_key]['clever_point_params']['point'])) {
                if(Registry::get('addons.slx_clever_point.cost_to')=='customer') {
                    $cart['product_groups'][$group_key]['clever_point_params']['cost'] = slx_clever_point_get_cost();
                }
                else {
                    $cart['product_groups'][$group_key]['clever_point_params']['cost'] = 0;
                }
            }
            fn_save_cart_content($cart, $auth['user_id']);
            $clever_points[$group_key] = $cart['product_groups'][$group_key]['clever_point_params'];
        }

        return [CONTROLLER_STATUS_REDIRECT, 'checkout.checkout'];
    }
    if($mode == 'place_order') {
		$group_key = 0;
		$votes =
			isset($cart['product_groups'][$group_key]['clever_point_params']['point']['IsOperationalForCOD']) 
			&& !$cart['product_groups'][$group_key]['clever_point_params']['point']['IsOperationalForCOD'];
	    if ( $votes ) {
    		$cods = array_keys(Registry::get('addons.slx_clever_point.cod_payment_ids'));
    		if(in_array($cart['payment_id'], $cods)) {
    			fn_set_notification('W', __("warning"), __("slx_clever_point.point_does_not_accept_cod"));
    			return [CONTROLLER_STATUS_REDIRECT, 'checkout.checkout'];
    		}
    	}
    	
    }
}
if($mode=='checkout') {
    if (!empty($_REQUEST['payment_id'])) {
        $cods = array_keys(Registry::get('addons.slx_clever_point.cod_payment_ids'));
        if(in_array($_REQUEST['payment_id'], $cods)) {
            $point = slx_clever_point_get_selected_point_from_cart($cart, 0);
            if ($point) {
                if (isset($point['IsOperationalForCOD']) && $point['IsOperationalForCOD'] == false) {
                    $_REQUEST['payment_id']=0;
                    fn_set_notification('W', __("warning"), __("slx_clever_point.point_does_not_accept_cod"));
                }
            }
        }
    }
}
