{if $order_info.pickup_cost}
<tr class="ty-orders-summary__row">
    <td>{$order_info.payment_method.surcharge_title|default:__("slx_clever_point.clever_point_cost")}:&nbsp;</td>
    <td data-ct-orders-summary="summary-surchange">{include file="common/price.tpl" value=$order_info.pickup_cost}
    </td>
</tr>
{/if}