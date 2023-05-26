
To show pickup point in order printout, add the next snippet to your tempalte.

```twig
{% for pg in o.product_groups %}
{% if pg.clever_point_params %}
<p style="color: #787878; font-size: 14px; font-family: Helvetica, Arial, sans-serif; padding-bottom: 5px; margin: 0px;">{{ __("slx_clever_point.clever_point") }}:</p>
    <ul>
    <li>{{ pg.clever_point_params.point.ShortName }}</li>
    <li>{{ __("slx_clever_point.address") }}: {{ pg.clever_point_params.point.AddressLine1 }}, {{ pg.clever_point_params.point.City }}</li>
    <li>{{__("slx_clever_point.phone")}}: {{ pg.clever_point_params.point.Phones }}</li>
    <li>{{__("slx_clever_point.work_hours")}}: {% for wk in pg.clever_point_params.point.WorkHoursFormattedWithDaysV2 %}{{ wk}} {% endfor %}</li>
    </ul>
{% endif %}
{% endfor %}
```
