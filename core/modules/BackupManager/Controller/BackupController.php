<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\BackupManager\Controller;

use Psr\Http\Message\ResponseInterface;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Http\Response;
use Soosyze\Components\Http\Stream;

/**
 * @method \Soosyze\Core\Modules\BackupManager\Services\BackupManager backupmanager()
 * @method \Soosyze\Core\Modules\Template\Services\Templating         template()
 */
class BackupController extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/services.php';
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routes.php';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function admin(): ResponseInterface
    {
        $backups = [];
        if ($isRepository = self::backupmanager()->isRepository()) {
            $backups = self::backupmanager()->listBackups();
        }
        $doBackupRoute = $backups === []
            ? null
            : self::router()->generateUrl('backupmanager.delete.all');

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fas fa-file-archive"></i>',
                    'title_main' => t('Backups manager')
                ])
                ->make('page.content', 'backupmanager/content-backup-admin.php', $this->pathViews, [
                    'backups'          => $backups,
                    'delete_all_route' => $doBackupRoute,
                    'do_backup_route'  => self::router()->generateUrl('backupmanager.dobackup'),
                    'is_repository'    => $isRepository,
                    'name_repository'  => self::backupmanager()->getRepository(),
                    'max_backups'      => self::config()->get('settings.max_backups')
        ]);
    }

    public function deleteAll(): ResponseInterface
    {
        $_SESSION[ 'messages' ] = self::backupmanager()->deleteAll()
            ? [ 'success' => [ t('Backups deleted successfuly') ] ]
            : [ 'errors' => [ t('Backups delete failed') ] ];

        return new Redirect(
            self::router()->generateUrl('backupmanager.admin'),
            302
        );
    }

    public function download(string $file): ResponseInterface
    {
        if ($content = self::backupmanager()->getBackup($file)) {
            return new Response(200, new Stream($content), [
                'content-type'        => 'application/zip',
                'content-disposition' => 'attachement; filename="' . $file . '.zip"'
            ]);
        }

        return $this->get404();
    }

    public function restore(string $file): ResponseInterface
    {
        $_SESSION[ 'messages' ] = self::backupmanager()->restore($file)
            ? [ 'success' => [ t('Backup restored successfuly') ] ]
            : [ 'errors' => [ t('Backup restore failed') ] ];

        return new Redirect(
            self::router()->generateUrl('backupmanager.admin'),
            302
        );
    }

    public function delete(string $file): ResponseInterface
    {
        $_SESSION[ 'messages' ] = self::backupmanager()->delete($file)
            ? [ 'success' => [ t('Backup deleted successfuly') ] ]
            : [ 'errors' => [ t('Backup delete failed') ] ];

        return new Redirect(
            self::router()->generateUrl('backupmanager.admin'),
            302
        );
    }

    public function doBackup(): ResponseInterface
    {
        $_SESSION[ 'messages' ] = self::backupmanager()->doBackup()
            ? [ 'success' => [ t('Backup done successfuly') ] ]
            : [ 'errors' => [ t('Backup failed') ] ];

        return new Redirect(
            self::router()->generateUrl('backupmanager.admin'),
            302
        );
    }
}
