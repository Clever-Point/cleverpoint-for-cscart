{$cCost = slx_clever_point_get_cost_from_cart($cart)}
{if $cCost}
<tr>
    <td class="ty-checkout-summary__item">{__("slx_clever_point.clever_point_cost")}</td>
    <td class="ty-checkout-summary__item ty-right" data-ct-checkout-summary="shipping">
        <span>{include file="common/price.tpl" value=$cCost}</span>
    </td>
</tr>
{/if}