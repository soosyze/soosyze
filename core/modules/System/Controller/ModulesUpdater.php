<?php

namespace SoosyzeCore\System\Controller;

use Soosyze\Components\Http\Redirect;

class ModulesUpdater extends \Soosyze\Controller
{
    public function check()
    {
        if (self::migration()->isMigration()) {
            self::config()
                ->set('settings.module_update', true)
                ->set('settings.module_update_time', time());
        } else {
            $_SESSION[ 'messages' ][ 'success' ] = [ 'Pas de mise à jour' ];
            self::config()->set('settings.module_update_time', time());
        }

        return new Redirect(self::router()->getRoute('system.module.edit'));
    }

    public function updater()
    {
        self::migration()->migrate();
        self::config()->set('settings.module_update', false);
        $_SESSION[ 'messages' ][ 'success' ] = [ 'La mise à jour est un succes' ];

        return new Redirect(self::router()->getRoute('system.module.edit'));
    }
}
