<div class="clearfix">
    <h5 class="subheader">{__("slx_clever_point.shipments")}</h5>
    {foreach $shipments as $shipment}
        <div class="clearfix">
            <ul>
                <li>{__("slx_clever_point.awb_no")}: {$shipment.response.ShipmentAwb}</li>
                <li>{__("slx_clever_point.created_at")}: {$shipment.response.ShipmentStatusDt}</li>
                <li>{__("slx_clever_point.clever_point_cost")}
                    : {$shipment.response.ShipmentCost.Value} {$shipment.response.ShipmentCost.CurrencyCode}</li>
            </ul>
            <div style="margin-top:15px;text-align: right;">

                <a class="btn cm-post"
                   href="{"clever_point.voucher?awb=`$shipment.response.ShipmentAwb`&order_id=`$order_info.order_id`"|fn_url}"
                >{__("slx_clever_point.get_voucher")}</a>

                <a class="btn" target="_blank" 
                   href="{fn_url("clever_point.track?awb=`$shipment.response.ShipmentAwb`", 'C')}"
                >{__("slx_clever_point.track")}</a>
            </div>
            <div style="margin-top:15px;text-align: right;">

                <a class="btn btn-text cm-confirm cm-post"
                   href="{"clever_point.cancel?order_id=`$order_info.order_id`&awb=`$shipment.response.ShipmentAwb`"|fn_url}"
                >{__("slx_clever_point.cancel")}</a>
            </div>
        </div>
    {/foreach}
</div>