<?php
/**
 * @copyright weblive.gr
 * @author Panos <panos@salix.gr>
 * Created: 04/Απρ/2023
 * Time: 20:05
 */

namespace Slx\CleverPoint;

class OrderCPShipments extends AbstractModel3 {

    public function configure() {
        $this->tableName = '?:order_cp_shipments';
        $this->idColumn = 'id';
        $this->labelColumn = 'order_id';
        $this->hasStatusColumn = false;
    }

    protected function setupGetItemsComponents() {
    }

    public function deleteOrderShipment($orderId, $voucher) {
        list($items) = $this->getItems(['order_id'=>$orderId, 'voucher' => $voucher]);
        if($items) {
            foreach($items as $item) {
                $this->delete($item['id']);
            }
        }
    }
}