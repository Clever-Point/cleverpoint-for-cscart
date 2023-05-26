<h5 class="subheader">{__("slx_clever_point.clever_point")}:</h5>
<ul>
    <li>{$clever_point.ShortName}</li>
    <li>{__("slx_clever_point.address")}: {$clever_point.AddressLine1}
        , {$clever_point.City}</li>
    <li>{__("slx_clever_point.phone")}: {$clever_point.Phones}</li>
    <li>{__("slx_clever_point.work_hours")}
        : {implode(', ',$clever_point.WorkHoursFormattedWithDaysV2)}</li>
</ul>