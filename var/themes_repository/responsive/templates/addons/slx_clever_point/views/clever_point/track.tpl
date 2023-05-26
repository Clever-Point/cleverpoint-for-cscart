
{if $scans}
    <div class="send-info-title">
        {__("slx_clever_point.current_status")}: <strong>{$scans.MainData.ShipmentStatusName}</strong>
    </div>
    <div class="row-fluid">
        <div class="span4">
            <div class="send-info-box">
                <p class="box-track">{__("slx_clever_point.destination")}</p>
                <p class="box-country">{$scans.MainData.DeliveryStationName}</p>
                <p class="box-serial">{$scans.MainData.ShipmentOutboundAwb}</p>
            </div>
            <div class="send-info-box">
                <p class="box-track">{__("slx_clever_point.origin")}</p>
                <p class="box-country">{$scans.MainData.ShipperName}</p>
                <p class="box-serial">{$scans.MainData.ShipmentOutboundAwb}</p>
            </div>
        </div>
        <div class="span12">
            <div>
                <ul class="tracking-steps">
                    {foreach from=$scans.TrackingData item="scan" name="scans"}
                        <li class="tracking-step">
                            <div class="icon-con {if $smarty.foreach.scans.first} red{/if}"><i class="iconfont"></i></div>
                            <p>{$scan.TrackingNote}</p>
                            <p>{$scan.Timestamp|date_format:"`$settings.Appearance.date_format`, `$settings.Appearance.time_format`"} {$scan.StationName}</p>
                        </li>
                    {/foreach}
                </ul>
            </div>

        </div>
    </div>
    <p class="hint">{__("slx_clever_point.data_by_clever_point")}</p>
{else}
    <p>{__("slx_clever_point.no_tracking_information")}</p>
{/if}
{capture name="mainbox_title"}{__("slx_clever_point.tracking_order")}: {$order_id}{/capture}

<style>

    .tracking-steps {

    }

    .tracking-steps .tracking-step .icon-con {
        position: absolute;
        top: 8px;
        left: -4px;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #ccc;
    }
    .tracking-steps .tracking-step .icon-con .iconfont {
        position: absolute;
        top: 2px;
        left: 2.5px;
        color: #fff;
        font-size: 12px;
    }
    .tracking-steps .tracking-step .icon-con.red {
        background: #dd1144;
    }

    .iconfont {
        font-family: "iconfont" !important;
        font-size: 16px;
        font-style: normal;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    .tracking-steps .tracking-step:last-child::before {
        height: 0;
    }
    .tracking-steps .tracking-step:before {
        content: "";
        position: absolute;
        top: 8px;
        left: -1px;
        width: 1px;
        height: calc(100% + 7px);
        background: #ccc;
    }

    .tracking-steps .tracking-step {
        position: relative;
        margin-left: 8px;
        padding-left: 13px;
        padding-bottom: 12px;
    }
    .tracking-steps .tracking-step p {
        line-height: 1.3em;
        color: #999;
        padding-top: 0px;
    }
    .tracking-steps .tracking-step p:first-child {
        padding-top: 0px;
    }

    .tracking-steps .tracking-step p:last-child {
        padding-top: 0px;
    }


    .send-info-box {
        min-height: 75px;
        line-height: 1.5;
        color: #333;
        padding: 5px 9px 5px 6px;
        border: 1px solid #eee;
        border-left-color: rgb(238, 238, 238);
        border-left-style: solid;
        border-left-width: 1px;
        border-left: 4px solid #454444;
        border-left-color: rgb(69, 68, 68);
        border-radius: 3px;
        margin-bottom: 10px;

    }

    .send-info-box:first-child {
        border-left-color: #e62e05;
    }

    .send-info-box .box-track {
        text-align: right;
        margin-bottom: 3px;
    }

    .send-info-box .box-country {
        font-size: 16px;
    }

    .send-info-box .box-serial {
        color: #666;
    }
    .send-info-box p {
        margin-bottom: 0;
        margin-top: 0;
        padding: 0;
        font-size: 12px;
    }

    .send-info-title {
        margin-bottom: 20px;
        font-size: 16px;
        font-weight: bold;
        padding-bottom: 10px;
        border-bottom: 1px dashed #ececec;
    }

    .send-info-title strong {
        color: #e62e05;
    }
</style>