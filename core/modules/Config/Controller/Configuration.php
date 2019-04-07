<?php

namespace Config\Controller;

use Psr\Http\Message\UploadedFileInterface;
use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Util\Util;
use Soosyze\Components\Validator\Validator;

define('VIEWS_CONFIG', MODULES_CORE . 'Config' . DS . 'Views' . DS);
define('CONFIG_CONFIG', MODULES_CORE . 'Config' . DS . 'Config' . DS);

class Configuration extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathRoutes   = CONFIG_CONFIG . 'routing.json';
        $this->pathServices = CONFIG_CONFIG . 'service.json';
    }

    public function index($req)
    {
        if (($menu = $this->getMenuConfig())) {
            return $this->edit($menu[ 0 ][ 'key' ], $req);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'title_main' => '<i class="fa fa-cog"></i> Configuration'
                ])
                ->view('page.messages', [ 'infos' => [ 'Aucune configuration disponible' ] ])
                ->render('page.content', 'page-config.php', VIEWS_CONFIG, [
                    'form' => null
        ]);
    }

    public function edit($id, $req)
    {
        if (!($menu = $this->getMenuConfig())) {
            return $this->get404($req);
        }

        $data = self::config()->get('settings');

        $this->container->callHook("config.edit.$id.form.data", [ &$data, $id ]);
        if (isset($_SESSION[ 'inputs' ])) {
            $data = array_merge($data, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $form = new FormBuilder([
            'method'  => 'post',
            'action'  => self::router()->getRoute('config.update', [ ':id' => $id ]),
            'enctype' => 'multipart/form-data' ]);
        $this->container->callHook("config.edit.$id.form.generate", [ &$form, $data ]);

        $this->container->callHook("config.edit.$id.form", [ &$form, $data ]);

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }
        if (isset($_SESSION[ 'errors_keys' ])) {
            $form->addAttrs($_SESSION[ 'errors_keys' ], [ 'style' => 'border-color:#a94442;' ]);
            unset($_SESSION[ 'errors_keys' ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'title_main' => '<i class="fa fa-cog"></i> Configuration'
                ])
                ->view('page.messages', $messages)
                ->render('page.content', 'page-config.php', VIEWS_CONFIG, [
                    'form' => $form
                ])
                ->render('content.menu_config', 'menu-config.php', VIEWS_CONFIG, [
                    'menu' => $menu,
                    'id'   => $id
        ]);
    }

    public function update($id, $req)
    {
        $post      = $req->getParsedBody();
        $files     = $req->getUploadedFiles();
        $validator = (new Validator())->setInputs($post + $files);
        $route     = self::router()->getRoute('config.edit', [ ':id' => $id ]);
        $dataFiles = [];
        self::core()->callHook("config.update.$id.files", [ &$dataFiles ]);

        self::core()->callHook("config.update.$id.validator", [ &$validator ]);

        if ($validator->isValid()) {
            $data = [];
            self::core()->callHook("config.update.$id.before", [ &$validator, &$data,
                $id ]);
            foreach ($data as $key => $value) {
                self::config()->set('settings.' . $key, $value);
            }
            foreach ($dataFiles as $file) {
                $this->saveFile($file, $validator);
            }
            self::core()->callHook("config.update.$id.after", [ &$validator, $id ]);

            $_SESSION[ 'messages' ][ 'success' ] = [ 'Configuration Enregistrée' ];

            return new Redirect($route);
        }

        $server = $req->getServerParams();
        if (empty($post) && empty($files) && isset($server[ 'CONTENT_LENGTH' ]) && $server[ 'CONTENT_LENGTH' ] > 0) {
            $_SESSION[ 'messages' ][ 'errors' ] = [ 'La quantité totales des données reçues '
                . 'dépasse la valeur maximale autorisée par la directive post_max_size '
                . 'de votre fichier php.ini' ];
            $_SESSION[ 'errors_keys' ]          = [];
        } else {
            $_SESSION[ 'inputs' ]               = $validator->getInputsWithout($dataFiles);
            $_SESSION[ 'messages' ][ 'errors' ] = $validator->getErrors();
            $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();
        }

        return new Redirect($route);
    }

    protected function getMenuConfig()
    {
        $menu = [];
        $this->container->callHook('config.edit.menu', [ &$menu ]);
        sort($menu);
        foreach ($menu as $key => &$link) {
            if (!$this->container->callHook('app.granted', [ $link[ 'key' ] . '.config.manage' ])) {
                unset($menu[ $key ]);

                continue;
            }
            $link[ 'link' ] = self::router()->getRoute('config.edit', [ ':id' => $link[ 'key' ] ]);
        }

        return array_values($menu);
    }

    private function saveFile($key, $validator)
    {
        self::file()
            ->add($validator->getInput($key), $validator->getInput("file-name-$key"))
            ->moveTo($key)
            ->callGet(function ($key) {
                return self::config()->get("settings.$key");
            })
            ->callMove(function ($key, $move) {
                self::config()->set("settings.$key", $move);
            })
            ->callDelete(function ($key) {
                self::config()->set("settings.$key", '');
            })
            ->save();
    }
}
