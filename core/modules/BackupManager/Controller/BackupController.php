<?php

namespace SoosyzeCore\BackupManager\Controller;

use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Http\Response;
use Soosyze\Components\Http\Stream;

class BackupController extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/service.json';
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routes.php';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function index()
    {
        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fas fa-file-archive"></i>',
                    'title_main' => t('Backups manager')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'page-index.php', $this->pathViews, [
                    'backups'          => self::backupservice()->listBackups(),
                    'max_backups'      => self::config()->get('settings.max_backups'),
                    'do_backup_route'  => self::router()->getRoute('backupmanager.dobackup'),
                    'delete_all_route' => self::router()->getRoute('backupmanager.delete.all')
        ]);
    }

    public function deleteAll()
    {
        $_SESSION[ 'messages' ] = self::backupservice()->deleteAll()
            ? [ 'success' => [ t('Backups deleted successfuly') ] ]
            : [ 'errors' => [ t('Backups delete failed') ] ];

        return new Redirect(
            self::router()->getRoute('backupmanager.index')
        );
    }

    public function download($path)
    {
        if ($content = self::backupservice()->getBackup($path)) {
            return new Response(200, new Stream($content), [
                'content-type'        => 'application/zip',
                'content-disposition' => 'attachement; filename="' . $path . '.zip"'
            ]);
        }

        return $this->get404();
    }

    public function restore($path)
    {
        $_SESSION[ 'messages' ] = self::backupservice()->restore($path)
            ? [ 'success' => [ t('Backup restored successfuly') ] ]
            : [ 'errors' => [ t('Backup restore failed') ] ];

        return new Redirect(
            self::router()->getRoute('backupmanager.index')
        );
    }

    public function delete($path, $req)
    {
        $_SESSION[ 'messages' ] = self::backupservice()->delete($path)
            ? [ 'success' => [ t('Backup deleted successfuly') ] ]
            : [ 'errors' => [ t('Backup delete failed') ] ];

        return new Redirect(
            self::router()->getRoute('backupmanager.index')
        );
    }

    public function doBackup()
    {
        $_SESSION[ 'messages' ] = self::backupservice()->doBackup()
            ? [ 'success' => [ t('Backup done successfuly') ] ]
            : [ 'errors' => [ t('Backup failed') ] ];

        return new Redirect(
            self::router()->getRoute('backupmanager.index')
        );
    }
}
