{foreach $order_info.product_groups as $group}
    {if $group.clever_point_params.point}
        <p>{__("slx_clever_point.clever_point")}:</p>
        <ul>
            <li>{$group.clever_point_params.point.ShortName}</li>
            <li>{__("slx_clever_point.address")}: {$group.clever_point_params.point.AddressLine1}, {$group.clever_point_params.point.City}</li>
            <li>{__("slx_clever_point.phone")}: {$group.clever_point_params.point.Phones}</li>
            <li>{__("slx_clever_point.work_hours")}: {implode(', ',$group.clever_point_params.point.WorkHoursFormattedWithDaysV2)}</li>
        </ul>
    {/if}
{/foreach}