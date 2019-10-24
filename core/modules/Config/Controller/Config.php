<?php

namespace SoosyzeCore\Config\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Validator\Validator;

class Config extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/service.json';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
        $this->dirRoutes    = dirname(__DIR__) . '/Config/routes.php';
    }

    public function index($req)
    {
        if ($menu = $this->getMenuConfig()) {
            $key = count($menu) ? array_keys($menu)[0] : null;

            return $this->edit($key, $req);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'title_main' => '<i class="fa fa-cog" aria-hidden="true"></i> ' . t('Configuration')
                ])
                ->view('page.messages', [ 'infos' => [ t('No configuration available') ] ])
                ->make('page.content', 'page-config.php', $this->pathViews, [
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
        $this->container->callHook("config.edit.$id.form.generate", [ &$form, $data, $req ]);

        $this->container->callHook("config.edit.$id.form", [ &$form, $data, $req ]);

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
                    'title_main' => '<i class="fa fa-cog" aria-hidden="true"></i> ' . t('Configuration')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'page-config.php', $this->pathViews, [
                    'form' => $form
                ])
                ->make('content.menu_config', 'submenu-config.php', $this->pathViews, [
                    'menu' => $menu,
                    'id'   => $id
        ]);
    }

    public function update($id, $req)
    {
        $post      = $req->getParsedBody();
        $files     = $req->getUploadedFiles();
        $validator = (new Validator())->setInputs($post + $files);
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

            $_SESSION[ 'messages' ][ 'success' ] = [ t('Saved configuration') ];
            $route     = self::router()->getRoute('config.edit', [ ':id' => $id ]);

            return new Redirect($route);
        }

        $server = $req->getServerParams();
        if (empty($post) && empty($files) && isset($server[ 'CONTENT_LENGTH' ]) && $server[ 'CONTENT_LENGTH' ] > 0) {
            $_SESSION[ 'messages' ][ 'errors' ] = [
                t('The total amount of data received exceeds the maximum value allowed by the post_max_size directive in your php.ini file.')
            ];
            $_SESSION[ 'errors_keys' ]          = [];
        } else {
            $_SESSION[ 'inputs' ]               = $validator->getInputsWithout($dataFiles);
            $_SESSION[ 'messages' ][ 'errors' ] = $validator->getErrors();
            $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();
        }
        $route     = self::router()->getRoute('config.edit', [ ':id' => $id ]);
        
        return new Redirect($route);
    }

    protected function getMenuConfig()
    {
        $menu = [];
        $this->container->callHook('config.edit.menu', [ &$menu ]);
        ksort($menu);
        $all = $this->container->callHook('app.granted', [ 'config.manage' ]);
        foreach ($menu as $key => &$link) {
            $manage = $this->container->callHook('app.granted', [ $key . '.config.manage' ]);
            if ($all || $manage) {
                $link[ 'link' ] = self::router()->getRoute('config.edit', [ ':id' => $key ]);

                continue;
            }
            unset($menu[$key]);
        }

        return $menu;
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
