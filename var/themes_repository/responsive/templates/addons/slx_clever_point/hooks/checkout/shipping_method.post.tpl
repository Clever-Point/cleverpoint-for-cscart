{if $cart.chosen_shipping.$group_key == $shipping.shipping_id
&& slx_clever_point_is_clever_point_shipping_method($shipping.shipping_id)
&& slx_clever_point_is_customer_in_gr($cart)}
    {$defaultPickupPoint = $addons.slx_clever_point.defaultCleverPoint}
    {$selectedPickupPoint = slx_clever_point_get_selected_point_from_cart($cart, $group_key)}
    {if $selectedPickupPoint}
        {$defaultPickupPoint = $selectedPickupPoint.StationId}
    {/if}
    {if $addons.slx_clever_point.map_display=='modal'}
    <div id="clever-point-modal" class="hidden">
        <div id="clevermap" style="width:100%; height: {max(500,$addons.slx_clever_point.map_height)}px;"></div>
    </div>
    <div class="wbl-button-container">
    <a class="ty-btn ty-btn__primary cm-dialog-opener" data-ca-target-id="clever-point-modal" title="{__("slx_clever_point.select_cleverpoint")}">{__("slx_clever_point.select_cleverpoint")}</a>
    </div>
    {else}
    <div id="clevermap" style="width:100%; height: {max(500,$addons.slx_clever_point.map_height)}px;"></div>
    {/if}

    <div class="litecheckout__field litecheckout__field--xsmall litecheckout__field--input {$inputs_extra_class}" data-ca-error-message-target-method="append">
        <input class="litecheckout__input" placeholder=" "
               id="clever_point_selection_{$group_key}"
               type="hidden" name="clever_point[selection]"
               value="{if $selectedPickupPoint}{$selectedPickupPoint.ShortName}{/if}"
               data-ca-lite-checkout-field="clever_point.selection"
               data-clever-point-station-id="{$defaultPickupPoint}"
               autocomplete="" aria-label="Clever point" title="Clever point"
               readonly
        />
        <label class="litecheckout__label  cm-required cm-trim hidden"
               for="clever_point_selection_{$group_key}">{__("slx_clever_point.clever_point")}</label>
            <div class="clearfix wbl-clever-point-info-block" id="checkout_info_pickup_point">
        {if $selectedPickupPoint}
                <p>{__("slx_clever_point.clever_point")}:</p>
                <ul>
                    <li>{$selectedPickupPoint.ShortName}</li>
                    <li>{__("slx_clever_point.address")}: {$selectedPickupPoint.AddressLine1}, {$selectedPickupPoint.City}</li>
                    <li>{__("slx_clever_point.phone")}: {$selectedPickupPoint.Phones}</li>
                    <li>{__("slx_clever_point.work_hours")}: {implode(', ',$selectedPickupPoint.WorkHoursFormattedWithDaysV2)}</li>
                    <li>{__("slx_clever_point.accept_cod")}: {if $selectedPickupPoint.IsOperationalForCOD}{__("yes")}{else}{__("slx_clever_point.point_does_not_accept_cod")}{/if}</li>
                </ul>
        {/if}
                <!--checkout_info_pickup_point--></div>
    </div>
    <p>
        <a class="cm-dialog-opener cm-dialog-auto-size" data-ca-target-id="what_is_clever_point">{__("slx_clever_point.what_is_clever_point_button")}</a>
    </p>
    <div id="what_is_clever_point" class="hidden" title="{__("slx_clever_point.what_is_clever_point_button")}">
        {include file="addons/slx_clever_point/components/what_is.tpl"}
    </div>

    <script type="text/javascript" src="https://test.cleverpoint.gr/portal/content/clevermap_v2/script/cleverpoint-map.js"></script>
    <script type="text/javascript">
        (function (_, $) {
            $.ceEvent('on', 'ce.commoninit', function (context) {
                if($('#clevermap',context).length==0) {
                    return;
                }
                if($('#clevermap').data('initialized')==1) {
                    return;
                }
                $('#clevermap').data('initialized', 1);
                let inp = $("#clever_point_selection_{$group_key}");
                let stationId = inp.data('clever-point-station-id');
                clevermap({
                    selector: '#clevermap',
                    cleverPointKey: '{$addons.slx_clever_point.api_key}',
                    {if $addons.slx_clever_point.map_provider=="arcgis"}arcgisMapKey: '{$addons.slx_clever_point.mapKey}', {/if}
                    {if $addons.slx_clever_point.map_provider=="google"}googleMapKey: '{$addons.slx_clever_point.mapKey}', {/if}

                    header: {if $addons.slx_clever_point.header=="Y"}true{else}false{/if},
                    defaultAddress: {if !empty($addons.slx_clever_point.defaultAddress)}'{$addons.slx_clever_point.defaultAddress}'{else}null{/if},
                    defaultCoordinates: {if !empty($addons.slx_clever_point.defaultCoordinates)}'{$addons.slx_clever_point.defaultCoordinates}'{else}null{/if},
                    defaultCleverPoint: stationId,
                    singleSelect: {if $addons.slx_clever_point.singleSelect=="Y"}true{else}false{/if},
                    display: {
                        addressBar: {if $addons.slx_clever_point.addressBar=="Y"}true{else}false{/if},
                        pointList: {if $addons.slx_clever_point.pointList=="Y"}true{else}false{/if},
                        pointInfoType: '{$addons.slx_clever_point.pointInfoType}'
                    },
                    filters: {
                        codAmount: 0
                    },
                    onclear: () => {
                        console.log('point cleared');
                        let cargo = {
                            group_key : '{$group_key}',
                            point : null
                        }
                        $("#clever_point_selection_{$group_key}").val("");
                        let url = fn_url('checkout.clever_point_options');
                        let result_ids = 'checkout_info_summary_*,checkout_info_order_info_*,checkout_info_pickup_point';
                        Tygh.$.ceAjax('request', url, {
                            data: cargo,
                            result_ids: result_ids,
                            method: 'post',
                            full_render: true
                        });
                    },
                    onselect: (point) => {
                        console.log('selected', point);
                        let cargo = {
                            group_key : '{$group_key}',
                            point : point
                        }
                        $("#clever_point_selection_{$group_key}").val(point.ShortName);
                        let url = fn_url('checkout.clever_point_options');
                        let result_ids = 'checkout_info_summary_*,checkout_info_order_info_*,checkout_info_pickup_point';
                        Tygh.$.ceAjax('request', url, {
                            data: cargo,
                            result_ids: result_ids,
                            method: 'post',
                            full_render: true
                        });
                    },
                    oninitialized: () => {
                        console.log('initialized');
                        $('#clevermap').data('initialized', 1);
                    }

                });
            });
        }(Tygh, Tygh.$));
    </script>
{/if}
