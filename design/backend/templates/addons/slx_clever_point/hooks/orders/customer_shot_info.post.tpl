{if $addons.slx_clever_point.order_handling=='int'}

    {$clever_point =slx_clever_point_get_clever_point_from_order($order_info)}
    {if $clever_point}
        <div class="well orders-right-pane form-horizontal">
            {include file="common/subheader.tpl" title=__("slx_clever_point.clever_point")}
            {include file="addons/slx_clever_point/components/clever_point_info.tpl"}
            <hr/>
            {if slx_clever_point_can_create_for_orders($order_info)}
                {include file="addons/slx_clever_point/components/create_button.tpl"}
            {/if}
            {if $order_info.clever_point_shipments}
                {include file="addons/slx_clever_point/components/shipments.tpl" shipments=$order_info.clever_point_shipments}
            {/if}
        </div>
    {/if}

{/if}