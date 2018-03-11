<?php
$settings = array();
$tmp = array(
//    'parameters' => array(
//        'xtype' => 'textfield',
//        'value' => '',
//        'area' => PKG_NAME_LOWER . '.main',
//    ),
);

foreach ($tmp as $k => $v) {
    /* @var modSystemSetting $setting */
    $setting = $modx->newObject('modSystemSetting');
    $setting->fromArray(array_merge(
        array(
            'key' => PKG_NAME_LOWER . '.' . $k,
            'namespace' => PKG_NAME_LOWER,
        ), $v
    ), '', true, true);

    $settings[] = $setting;
}
unset($tmp);

return $settings;