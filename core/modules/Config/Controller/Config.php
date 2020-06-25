<?php

namespace SoosyzeCore\Config\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Validator\Validator;

class Config extends \Soosyze\Controller
{
    protected $pathViews;
    
    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/service.json';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routes.php';
    }

    public function index($req)
    {
        if (($menu = $this->getMenuConfig()) && count($menu)) {
            return $this->getConfig($menu, array_keys($menu)[ 0 ], $req);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-cog" aria-hidden="true"></i>',
                    'title_main' => t('Configuration')
                ])
                ->make('page.content', 'page-config.php', $this->pathViews, [
                    'form' => null
        ]);
    }

    public function edit($id, $req)
    {
        if ($menu = $this->getMenuConfig()) {
            return $this->getConfig($menu, $id, $req);
        }

        return $this->get404($req);
    }

    public function update($id, $req)
    {
        if (!($menu = $this->getMenuConfig()) || !isset($menu[ $id ])) {
            return $this->get404($req);
        }

        $validator = (new Validator())
            ->setInputs($req->getParsedBody() + $req->getUploadedFiles());
        $inputsFile = [];

        $config = $this->container->get("$id.hook.config");

        $config->validator($validator);
        $config->files($inputsFile);

        $validator->addRule('token_' . $id . '_config', 'token');

        if ($validator->isValid()) {
            $data = [];

            $config->before($validator, $data, $id);
            foreach ($data as $key => $value) {
                self::config()->set('settings.' . $key, $value);
            }
            foreach ($inputsFile as $file) {
                $this->saveFile($file, $validator);
            }
            $config->after($validator, $data, $id);

            $_SESSION[ 'messages' ][ 'success' ] = [ t('Saved configuration') ];

            return new Redirect(
                self::router()->getRoute('config.edit', [ ':id' => $id ])
            );
        }

        if ($req->isMaxSize()) {
            $_SESSION[ 'messages' ][ 'errors' ][] = t('The total amount of data received exceeds the maximum value allowed by the post_max_size directive in your php.ini file.');
            $_SESSION[ 'errors_keys' ]            = [];
        } else {
            $_SESSION[ 'inputs' ]               = $validator->getInputsWithout($inputsFile);
            $_SESSION[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();
            $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();
        }

        return new Redirect(
            self::router()->getRoute('config.edit', [ ':id' => $id ])
        );
    }

    protected function getConfig($menu, $id, $req)
    {
        if (!isset($menu[ $id ])) {
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
            'enctype' => 'multipart/form-data'
        ]);

        $config = $this->container->get("$id.hook.config");
        $config->form($form, $data, $req);

        $form->token('token_' . $id . '_config')
            ->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ]);
        $this->container->callHook("config.edit.$id.form", [ &$form, $data, $req ]);

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }
        if (isset($_SESSION[ 'errors_keys' ])) {
            $form->addAttrs($_SESSION[ 'errors_keys' ], [ 'class' => 'is-invalid' ]);
            unset($_SESSION[ 'errors_keys' ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-cog" aria-hidden="true"></i>',
                    'title_main' => t('Configuration')
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
            unset($menu[ $key ]);
        }

        return $menu;
    }

    private function saveFile($key, $validator)
    {
        $dir = self::core()->getSettingEnv('files_public', 'app/files') . '/config';

        self::file()
            ->add($validator->getInput($key), $validator->getInput("file-name-$key"))
            ->setName($key)
            ->setPath($dir)
            ->setResolvePath()
            ->callGet(function ($key, $name) {
                return self::config()->get("settings.$key");
            })
            ->callMove(function ($key, $name, $move) use ($dir) {
                self::config()->set("settings.$key", "$dir/$name");
            })
            ->callDelete(function ($key, $name) {
                self::config()->set("settings.$key", '');
            })
            ->save();
    }
}
