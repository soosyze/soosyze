<?php

namespace System\Controller;

define('VIEWS_SYSTEM', MODULES_CORE . 'System' . DS . 'Views' . DS);
define('CONFIG_SYSTEM', MODULES_CORE . 'System' . DS . 'Config' . DS);

class System extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathServices = CONFIG_SYSTEM . 'service.json';
        $this->pathRoutes   = CONFIG_SYSTEM . 'routing.json';
    }

    public function maintenance()
    {
        return self::template()
                ->view('page', [
                    'title_main' => '<i class="glyphicon glyphicon-cog" aria-hidden="true"></i> Site en maintenance'
                ])
                ->render('page.content', 'page-maintenance.php', VIEWS_SYSTEM)
                ->withStatus(503);
    }
}
