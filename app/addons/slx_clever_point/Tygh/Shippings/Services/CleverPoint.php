<?php
/**
 * @copyright weblive
 * @author Panos <panos@salix.gr>
 * Created: 01/Απρ/2023
 * Time: 15:55
 */

namespace Tygh\Shippings\Services;

use Tygh\Shippings\IService;
use Tygh\Shippings\IPickupService;
use Tygh\Shippings\Shippings;
use Tygh\Registry;
use Tygh\Tygh;

class CleverPoint {

    public static function getInfo() {
        return array(
            'name' => __('carrier_cleverpoint'),
            'tracking_url' => fn_url('cleverpoint.tracking','C').'&tracking_no=%s'
        );
    }
}