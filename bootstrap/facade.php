<?php

/** @phpstan-ignore-next-line */
if ($timezone = \Core::getInstance()->get('config')->get('settings.timezone')) {
    date_default_timezone_set($timezone);
}
/** @phpstan-ignore-next-line */
if (\Core::getInstance()->get('config')->get('settings.lang') === 'fr') {
    setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
    Soosyze\Components\Validator\Validator::setMessagesGlobal(
        require_once 'validator_messages_fr.php'
    );
}

function t(string $str, array $vars = []): string
{
    /** @phpstan-ignore-next-line */
    return \Core::getInstance()->get('translate')->t($str, $vars);
}

function xss(string $str): string
{
    /** @phpstan-ignore-next-line */
    return \Core::getInstance()->get('xss')->filter($str);
}

require_once 'validator_custom.php';
require_once 'template.php';
