<?php

if ($timezone = \Core::getInstance()->get('config')->get('settings.timezone')) {
    date_default_timezone_set($timezone);
}

function t($str, $vars = [])
{
    return \Core::getInstance()->get('translate')->t($str, $vars);
}

require_once 'validator_custom.php';
