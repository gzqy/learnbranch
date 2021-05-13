<?php

return array(
    'device/add/?([0-9]*)'=>'device/add/$1',
    'device/GroupAdd/?([0-9]*)'=>'device/GroupAdd/$1',
    'contact/add/?([0-9]*)'=>'contact/add/$1',
    'contact/GroupAdd/?([0-9]*)'=>'contact/GroupAdd/$1',
    'admins/add/?([0-9]*)'=>'admins/add/$1',
    'admins/logs/?([0-9]*)'=>'admins/logs/$1',
    '([0-9]*)/Line'=>'Device/Line/$1',
    '([0-9]*)/Logs'=>'Device/Logs/$1',
    '([0-9]*)/Case'=>'Device/Case/$1',
    '([0-9]*)/Statistics'=>'Device/Statistics/$1',
    '([0-9]*)/monitor'=>'index/monitor/$1',
    '([0-9]*)/toExcel'=>'Device/toExcel/$1',
    '([0-9]*)/sendMail'=>'Device/sendMail/$1',
);
?>
