<?php
echo 33;die;
$path = './public/CSV/'.'app设备'.date("Y-m-d H:i:s").'.csv';
if (!is_file($path)){
    touch($path);
}
