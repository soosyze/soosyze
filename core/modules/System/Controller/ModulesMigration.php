<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\System\Controller;

use Psr\Http\Message\ResponseInterface;
use Soosyze\Components\Http\Redirect;

/**
 * @method \Soosyze\Core\Modules\System\Services\Migration   migration()
 * @method \Soosyze\Core\Modules\QueryBuilder\Services\Query query()
 */
class ModulesMigration extends \Soosyze\Controller
{
    public function check(): ResponseInterface
    {
        if (self::migration()->isMigration() || self::migration()->isCoreVersionMigrate()) {
            self::config()->set('settings.module_update', true);
        } else {
            $_SESSION[ 'messages' ][ 'success' ][] = t('Your site is up to date');
        }

        return new Redirect(self::router()->generateUrl('system.module.edit'), 302);
    }

    public function update(): ResponseInterface
    {
        try {
            self::migration()->migrate();
            self::migration()->migrateCoreVersion();

            self::config()
                ->set('settings.module_update', false)
                ->set('settings.module_update_time', time());

            $_SESSION[ 'messages' ][ 'success' ][] = t('The update is a success');
        } catch (\Exception $e) {
            $_SESSION[ 'messages' ][ 'errors' ][] = t('An error occurred during the update');
            /* Initialise le service de la base de données car celui-ci est un singleton */
            self::query()->init();
        }

        return new Redirect(self::router()->generateUrl('system.module.edit'), 302);
    }
}
