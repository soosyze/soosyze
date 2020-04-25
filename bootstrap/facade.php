<?php

if ($timezone = \Core::getInstance()->get('config')->get('settings.timezone')) {
    date_default_timezone_set($timezone);
}
if ($timezone = \Core::getInstance()->get('config')->get('settings.lang') === 'fr') {
    setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
    Soosyze\Components\Validator\Validator::setMessagesGlobal(
        require_once 'validator_messages_fr.php'
    );
}

function t($str, $vars = [])
{
    return \Core::getInstance()->get('translate')->t($str, $vars);
}

require_once 'validator_custom.php';
