<?php

namespace SoosyzeCore\System\Controller;

use Soosyze\Components\Http\Redirect;

class ModulesMigration extends \Soosyze\Controller
{
    public function check()
    {
        if (self::migration()->isMigration()) {
            self::config()->set('settings.module_update', true);
        } else {
            $_SESSION[ 'messages' ][ 'success' ] = [ t('Your site is up to date') ];
        }

        return new Redirect(self::router()->getRoute('system.module.edit'), 302);
    }

    public function update()
    {
        try {
            self::migration()->migrate();
            $_SESSION[ 'messages' ][ 'success' ] = [ t('The update is a success') ];
        } catch (\Exception $e) {
            $_SESSION[ 'messages' ][ 'error' ] = [ t('An error occurred during the update') ];
        }

        return new Redirect(self::router()->getRoute('system.module.edit'), 302);
    }
}
