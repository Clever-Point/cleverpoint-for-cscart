<?php
/**
 * @copyright weblive.gr
 * @author Panos <panos@salix.gr>
 * Created: 01/Απρ/2023
 * Time: 18:15
 */

use Tygh\Domain\SoftwareProduct\Version;

$version = new Version(PRODUCT_VERSION);
if ($version->lowerThan(new Version('4.11.5'))) {
    fn_register_hooks(
        'calculate_cart_post'
    );
}
else {
    fn_register_hooks(
        'calculate_cart_content_after_shipping_calculation',
        'calculate_cart'
    );
}

fn_register_hooks(
    'get_order_info',
    'shippings_get_shippings_list_post',
    'form_cart'
);