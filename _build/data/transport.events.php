<?php
$events = array();

$tmp = array(
    'OnParseTemplate' => array(
        'name' => 'OnParseTemplate',
        'service' => 5,
        'groupname' => 'System',
    ),
);

foreach ($tmp as $k => $v) {
    /** @var modEvent $event */
    $event = $modx->newObject('modEvent');
    /** @noinspection PhpUndefinedVariableInspection */
    $event->fromArray(array(
        'name' => $v['name'],
        'service' => $v['service'],
        'groupname' => $v['groupname'],
    ), '', true, true);
    $events[] = $event;
}

return $events;