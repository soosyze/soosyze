<?php

namespace SoosyzeCore\FileManager\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\FileManager\Form\FormPermission;
use SoosyzeCore\FileManager\Services\FileManager;

class Profil extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function create($req)
    {
        $values = [];
        if (isset($_SESSION[ 'inputs' ])) {
            $values = $_SESSION[ 'inputs' ];
            unset($_SESSION[ 'inputs' ]);
        }
        $this->container->callHook('filemanager.profil.create.form.data', [ &$values ]);

        $action = self::router()->getRoute('filemanager.profil.store');
        $form   = (new FormPermission([ 'method' => 'post', 'action' => $action ]))
            ->setValues($values)
            ->roles(self::query()->from('role')->fetchAll())
            ->makeFields();

        $this->container->callHook('filemanager.profil.create.form', [ &$form, $values ]);

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }
        if (isset($_SESSION[ 'errors_keys' ])) {
            unset($_SESSION[ 'errors_keys' ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-user" aria-hidden="true"></i>',
                    'title_main' => t('Add a files permission')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'filemanager/content-file_permission-form.php', $this->pathViews, [
                    'form' => $form
        ]);
    }

    public function store($req)
    {
        $validator = $this->getValidator($req);

        $this->container->callHook('filemanager.profil.store.validator', [ &$validator ]);

        $validatorExtension = new Validator();

        $listExtension      = implode(',', FileManager::getExtAllowed());
        foreach ($validator->getInput('file_extensions') as $key => $extension) {
            $validatorExtension
                ->addRule($key, 'inarray:' . $listExtension)
                ->addLabel($key, $extension)
                ->addInput($key, $key);
        }
        $isValid = $validator->isValid() && $validatorExtension->isValid();

        if ($isValid) {
            $data = $this->getData($validator);

            $this->container->callHook('filemanager.profil.store.before', [
                $validator, &$data
            ]);

            self::query()
                ->insertInto('profil_file', array_keys($data))
                ->values($data)
                ->execute();

            $permissionFileId = self::schema()->getIncrement('profil_file');
            $this->storeProfilRole($validator, $permissionFileId);

            $this->container->callHook('filemanager.profil.store.after', [ $validator ]);

            $_SESSION[ 'messages' ][ 'success' ] = [ t('Saved configuration') ];
            $route                               = self::router()->getRoute('filemanager.profil.admin');

            return new Redirect($route);
        }

        $_SESSION[ 'inputs' ]               = $validator->getInputs();
        $_SESSION[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();
        $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();

        $route = self::router()->getRoute('filemanager.profil.create');

        return new Redirect($route);
    }

    public function edit($id, $req)
    {
        if (!($values = self::fileprofil()->find($id))) {
            return $this->get404($req);
        }
        $values[ 'file_extensions' ] = explode(',', $values[ 'file_extensions' ]);
        $values[ 'roles' ] = self::fileprofil()->getIdRolesUser($id);

        $this->container->callHook('filemanager.profil.edit.form.data', [ &$values ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $values = $_SESSION[ 'inputs' ];
            unset($_SESSION[ 'inputs' ]);
        }

        $action = self::router()->getRoute('filemanager.profil.update', [ ':id' => $id ]);
        $form   = (new FormPermission([ 'method' => 'post', 'action' => $action ]))
            ->roles(self::query()->from('role')->fetchAll())
            ->setValues($values)
            ->makeFields();

        $this->container->callHook('filemanager.profil.edit.form', [ &$form, $values ]);

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
                    'icon'       => '<i class="fa fa-user" aria-hidden="true"></i>',
                    'title_main' => t('Edit the files permission')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'filemanager/content-file_permission-form.php', $this->pathViews, [
                    'form'               => $form,
                    'permission_submenu' => $this->getPermissionSubmenu('filemanager.profil.edit', $id)
        ]);
    }

    public function update($id, $req)
    {
        if (!self::fileprofil()->find($id)) {
            return $this->get404($req);
        }

        $validator = $this->getValidator($req);

        $this->container->callHook('filemanager.profil.update.validator', [
            &$validator, $id
        ]);

        $validatorExtension = new Validator();

        $listExtension = implode(',', FileManager::getExtAllowed());
        foreach ($validator->getInput('file_extensions') as $key => $extension) {
            $validatorExtension
                ->addRule($key, 'inarray:' . $listExtension)
                ->addLabel($key, $extension)
                ->addInput($key, $key);
        }
        $isValid = $validator->isValid() && $validatorExtension->isValid();

        if ($isValid) {
            $data = $this->getData($validator);

            $this->container->callHook('filemanager.profil.update.before', [
                $validator, &$data, $id
            ]);
            self::query()
                ->update('profil_file', $data)
                ->where('profil_file_id', '==', $id)
                ->execute();
            $this->updateProfilRole($validator, $id);
            $this->container->callHook('filemanager.profil.update.after', [
                $validator, $id
            ]);

            $_SESSION[ 'messages' ][ 'success' ] = [ t('Saved configuration') ];

            return new Redirect(self::router()->getRoute('filemanager.profil.admin'));
        }

        $_SESSION[ 'inputs' ]               = $validator->getInputs();
        $_SESSION[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();
        $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();

        return new Redirect(self::router()->getRoute('filemanager.profil.edit', [
                ':id' => $id
        ]));
    }

    public function remove($id, $req)
    {
        if (!self::fileprofil()->find($id)) {
            $this->get404($req);
        }

        $form = (new FormBuilder([
                'action' => self::router()->getRoute('filemanager.profil.delete', [
                    ':id' => $id
                ]),
                'method' => 'post',
                ]))
            ->group('profil-fieldset', 'fieldset', function ($form) {
                $form->legend('profil-legend', t('Delete files permission'))
                ->group('info-group', 'div', function ($form) {
                    $form->html('info', '<p:attr>:content</p>', [
                        ':content' => t('Warning ! The deletion of the files permission is final.')
                    ]);
                }, [ 'class' => 'alert alert-warning' ]);
            })
            ->token('token_file_permission')
            ->submit('submit', t('Delete'), [ 'class' => 'btn btn-danger' ])
            ->html('cancel', '<button:attr>:content</button>', [
                ':content' => t('Cancel'),
                'class'    => 'btn btn-default',
                'onclick'  => 'javascript:history.back();',
                'type'     => 'button'
            ]);

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-user" aria-hidden="true"></i>',
                    'title_main' => t('Delete files permission')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'filemanager/content-file_permission-form.php', $this->pathViews, [
                    'form'               => $form,
                    'permission_submenu' => $this->getPermissionSubmenu('filemanager.profil.remove', $id)
        ]);
    }

    public function delete($id, $req)
    {
        if (!self::fileprofil()->find($id)) {
            $this->get404($req);
        }
        $validator = (new Validator())
            ->addRule('token_file_permission', 'token')
            ->setInputs($req->getParsedBody());
        $this->container->callHook('filemanager.profil.delete.validator', [
            &$validator, $id
        ]);

        if ($validator->isValid()) {
            $this->container->callHook('filemanager.profil.delete.before', [
                $validator, $id
            ]);
            self::query()
                ->from('profil_file_role')
                ->delete()
                ->where('profil_file_id', '==', $id)
                ->execute();
            self::query()
                ->from('profil_file')
                ->delete()
                ->where('profil_file_id', '==', $id)
                ->execute();
            $this->container->callHook('filemanager.profil.delete.after', [
                $validator, $id
            ]);

            return new Redirect(self::router()->getRoute('filemanager.profil.admin'));
        }
        $_SESSION[ 'inputs' ]               = $validator->getInputs();
        $_SESSION[ 'messages' ][ 'errors' ] = $validator->getKeyErrors();

        return new Redirect(self::router()->getRoute('filemanager.profil.remove', [
                ':id' => $id
        ]));
    }

    protected function storeProfilRole($validator, $profilFileId)
    {
        self::query()->insertInto('profil_file_role', [
            'profil_file_id', 'role_id'
        ]);
        foreach (array_keys($validator->getInput('roles')) as $roleId) {
            self::query()->values([ $profilFileId, $roleId ]);
        }
        self::query()->execute();
    }

    protected function updateProfilRole($validator, $profilFileId)
    {
        self::query()
            ->from('profil_file_role')
            ->where('profil_file_id', '==', $profilFileId)
            ->delete()->execute();
        self::query()
            ->insertInto('profil_file_role', [ 'profil_file_id', 'role_id' ]);
        foreach (array_keys($validator->getInput('roles', [])) as $roleId) {
            self::query()->values([ $profilFileId, $roleId ]);
        }
        self::query()->execute();
    }

    protected function getValidator($req)
    {
        $validator = (new Validator())
            ->setRules([
                'folder_show'           => 'required|string|regex:/^\/([\-\:\w]+\/?)*$/',
                'folder_show_sub'       => 'bool',
                'profil_weight'         => 'required|between_numeric:1,50',
                'roles'                 => '!required|array',
                'folder_store'          => 'bool',
                'folder_update'         => 'bool',
                'folder_delete'         => 'bool',
                'folder_size'           => '!required|numeric|min_numeric:0',
                'file_store'            => 'bool',
                'file_update'           => 'bool',
                'file_delete'           => 'bool',
                'file_download'         => 'bool',
                'file_clipboard'        => 'bool',
                'file_copy'             => 'bool',
                'file_size'             => '!required|numeric|min_numeric:0',
                'file_extensions_all'   => 'bool',
                'file_extensions'       => '!required|array',
                'token_file_permission' => 'token'
            ])
            ->setMessages([
                'folder_show' => [
                    'regex' => [
                        'must' => t('The :label field should represent a tree structure of your files.')
                        . '<br>'
                        . t('It must start with a `\\` and may consist of alphanumeric characters `[a-z0-9]`, slashes `\\`, hyphens `-` and underscores `_`')
                    ]
                ]
            ])
            ->setLabels([
                'folder_show'     => t('Directory path'),
                'folder_show_sub' => t('Sub directories included'),
                'profil_weight'   => t('Weight'),
                'roles'           => t('User Roles'),
                'folder_size'     => t('Size limit by directory'),
                'file_size'       => t('Size limit by file'),
                'file_extensions' => t('File extensions'),
            ])
            ->setInputs($req->getParsedBody());

        if (!$validator->getInput('roles')) {
            $validator->addInput('roles', []);
        }
        if (!$validator->getInput('file_extensions')) {
            $validator->addInput('file_extensions', []);
        }

        return $validator;
    }

    protected function getData($validator)
    {
        return [
            'folder_show'         => $validator->getInput('folder_show'),
            'folder_show_sub'     => (bool) $validator->getInput('folder_show_sub'),
            'profil_weight'       => (int) $validator->getInput('profil_weight'),
            'folder_store'        => (bool) $validator->getInput('folder_store'),
            'folder_update'       => (bool) $validator->getInput('folder_update'),
            'folder_delete'       => (bool) $validator->getInput('folder_delete'),
            'folder_size'         => (int) $validator->getInput('folder_size'),
            'file_store'          => (bool) $validator->getInput('file_store'),
            'file_update'         => (bool) $validator->getInput('file_update'),
            'file_delete'         => (bool) $validator->getInput('file_delete'),
            'file_download'       => (bool) $validator->getInput('file_download'),
            'file_clipboard'      => (bool) $validator->getInput('file_clipboard'),
            'file_copy'           => (bool) $validator->getInput('file_copy'),
            'file_size'           => (int) $validator->getInput('file_size'),
            'file_extensions_all' => (bool) $validator->getInput('file_extensions_all'),
            'file_extensions'     => !$validator->getInput('file_extensions_all')
            ? implode(',', $validator->getInput('file_extensions', []))
            : ''
        ];
    }

    private function getPermissionSubmenu($keyRoute, $idPermission)
    {
        $menu = [
            [
                'key'        => 'filemanager.profil.edit',
                'request'    => self::router()->getRequestByRoute('filemanager.profil.edit', [
                    ':id' => $idPermission
                ]),
                'title_link' => t('Edit')
            ], [
                'key'        => 'filemanager.profil.remove',
                'request'    => self::router()->getRequestByRoute('filemanager.profil.remove', [
                    ':id' => $idPermission
                ]),
                'title_link' => t('Delete')
            ]
        ];

        $this->container->callHook('user.role.submenu', [ &$menu ]);

        foreach ($menu as $key => &$link) {
            if (!self::user()->isGrantedRoute($link[ 'request' ])) {
                unset($menu[ $key ]);

                continue;
            }
            $link[ 'link' ] = $link[ 'request' ]->getUri();
        }
        unset($link);

        return self::template()
                ->createBlock('filemanager/submenu-permission.php', $this->pathViews)
                ->addVars([
                    'key_route' => $keyRoute,
                    'menu'      => count($menu) === 1
                    ? []
                    : $menu
        ]);
    }
}
