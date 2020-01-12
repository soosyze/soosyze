<?php

namespace SoosyzeCore\BackupManager\Controller;

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
                    'title_main' => '<i class="fas fa-file-archive"></i> ' . t('Backups manager')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'page-index.php', $this->pathViews, [
                    'backups' => self::backupservice()->listBackups()
                ]);
    }
    
    public function restore($path)
    {
        if (self::backupservice()->restore($path)) {
            $_SESSION['messages']['success'][] = t('Backup restored successfuly');
        } else {
            $_SESSION['messages']['errors'][] = t('Backup restore failed !');
        }

        return new \Soosyze\Components\Http\Redirect(
            self::router()->getRoute('backupmanager.index')
        );
    }
    
    public function delete($path, $req)
    {
        if (self::backupservice()->delete($path)) {
            $_SESSION['messages']['success'][] = t('Backup deleted successfuly');
        } else {
            $_SESSION['messages']['errors'][] = t('Backup delete failed !');
        }

        return new \Soosyze\Components\Http\Redirect(
            self::router()->getRoute('backupmanager.index')
        );
    }
    
    public function doBackup()
    {
        if (self::backupservice()->doBackup()) {
            $_SESSION['messages']['success'][] = t('Backup done successfuly');
        } else {
            $_SESSION['messages']['errors'][] = t('Backup failed !');
        }

        return new \Soosyze\Components\Http\Redirect(
            self::router()->getRoute('backupmanager.index')
        );
    }
}
