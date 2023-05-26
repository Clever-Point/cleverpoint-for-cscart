<?php
/**
 * @copyright ArenaSoftwareS
 * @author Panos <panos@kartpay.com>
 * Created: 28/4/2021/Απρ/2021
 * Time: 12:43 μ.μ.
 */

namespace Slx\CleverPoint;

use Tygh\Enum\ProfileTypes;

class ProfileHelpers {

    public static function getVendorProfileFieldList($required = true) {
        $fields = fn_get_profile_fields('P', [], CART_LANGUAGE, ['profile_type' => ProfileTypes::CODE_SELLER]);
        $out = [];
        if (!$required) {
            $out[0] = '(None)';
        }
        foreach ($fields as $section => $sectionFields) {
            if ($section != 'E') {
                foreach ($sectionFields as $field) {
                    $sectionName = 'Contact';
                    if ($section == 'S') $sectionName = 'Shipping';
                    if ($section == 'B') $sectionName = 'Billing';

                    $out[$field['field_id']] = sprintf("%s (%s)", $field['description'], $sectionName);
                }
            }
        }
        return $out;
    }

    public static function getCustomerProfileFieldList($required = true) {
        $fields = fn_get_profile_fields('P');
        $out = [];
        if (!$required) {
            $out[0] = '(None)';
        }
        foreach ($fields as $section => $sectionFields) {
            if ($section != 'E') {
                foreach ($sectionFields as $field) {
                    $sectionName = 'Contact';
                    if ($section == 'S') $sectionName = 'Shipping';
                    if ($section == 'B') $sectionName = 'Billing';

                    $out[$field['field_id']] = sprintf("%s (%s)", $field['description'], $sectionName);
                }
            }
        }
        return $out;
    }

    public static function getProfileFieldValue($order_info, $fieldId, $allowToggle = false) {
        $out = '-';
        $field = self::getProfileField($fieldId);
        
        if (!empty($field) && $field['is_default'] == 'Y') {
            if (isset($order_info[$field['field_name']])) {
                $out = $order_info[$field['field_name']];
                if(strpos($field['field_name'], 'state')!==false) {
                    if(!empty($order_info[$field['field_name'].'_descr'])) {
                        $out = $order_info[$field['field_name'].'_descr'];
                    }
                }
                if (empty($out) && $allowToggle) {
                    $fieldName = self::toggleFieldProfileSection($field['field_name']);
                    $out = isset($order_info[$fieldName]) ? $order_info[$fieldName] : '';
                    if(strpos($field['field_name'], 'state')!==false) {
                        if(!empty($order_info[$field['field_name'].'_descr'])) {
                            $out = $order_info[$field['field_name'].'_descr'];
                        }
                    }
                }
            }
        } else {
            if (isset($order_info['fields'][$fieldId])) {
                $out = $order_info['fields'][$fieldId];
            }
        }
        return $out;
    }

    private static function toggleFieldProfileSection($fieldName) {
        $old = 'b_';
        $new = 's_';
        if (strpos($fieldName, 's_') === 0) {
            $new = 'b_';
            $old = 's_';
        }
        return str_replace($old, $new, $fieldName);
    }

    private static function getProfileField($field_id, $lang_code = DESCR_SL) {
        $profile_field = db_get_row(
            'SELECT * FROM ?:profile_fields AS pf'
            . ' LEFT JOIN ?:profile_field_descriptions AS pfd ON pf.field_id = pfd.object_id'
            . ' WHERE pf.field_id = ?i AND pfd.lang_code = ?s AND pfd.object_type = ?s',
            $field_id,
            $lang_code,
            'F'
        );

        return $profile_field;
    }
}