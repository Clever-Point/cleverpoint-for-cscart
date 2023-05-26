
<div id="shipment_wizard">
    <h4 class="subheader">{__("order")}: {$order_id}</h4>
    {include file="addons/slx_clever_point/components/clever_point_info.tpl"}
    <h4 class="subheader">{__("slx_clever_point.parameters")}</h4>
<form action="{""|fn_url}" method="post" name="clever_point_form" class="form-horizontal">
    <input type="hidden" name="order_id" value="{$order_id}" />
    <fieldset>
        <div class="control-group">
            <label class="control-label cm-required" for="elm_num_of_parcels">{__("slx_clever_point.num_of_parcels")}</label>
            <div class="controls">
                <input type="number" name="package[num_of_parcels]" id="elm_num_of_parcels" value="1" />
            </div>
        </div>
        <div class="control-group">
            <label class="control-label cm-required" for="elm_package_weight">{__("slx_clever_point.package_weight")}</label>
            <div class="controls">
                <input type="number" name="package[weight]" step="0.001"  id="elm_package_weight" value="{$package.weight}" />
            </div>
        </div>
        <div class="control-group">
            <label class="control-label cm-required" for="elm_cod_amount">{__("slx_clever_point.cod_amount")}</label>
            <div class="controls">
                <input type="number" name="package[cod_amount]" id="elm_cod_amount" value="{$package.length}" />
            </div>
        </div>
        <div class="control-group">
            <label class="control-label cm-required" for="elm_courier_voucher">{__("slx_clever_point.courier_voucher")}</label>
            <div class="controls">
                <input type="number" name="package[courier_voucher]" id="elm_courier_voucher" value="" />
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" for="elm_courier">{__("slx_clever_point.courier")}</label>
            <div class="controls">
                <select name="package[courier]" id="elm_courier" class="input-large form-control">
                    {foreach from=$couriers item="courier"}
                        <option value="{$courier.Id}">{$courier.Name}</option>
                    {/foreach}
                </select>
            </div>
        </div>
    </fieldset>
    <div class="buttons-container">
        <input type="submit" class="btn btn-primary" name="dispatch[clever_point.generate]" value="{__("create")}" />
        {include file="addons/slx_clever_point/components/close_popup.tpl"}
    </div>

</form>
</div>
