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

    public function admin($req)
    {
        $profils = self::query()->from('profil_file')->orderBy('profil_weight')->fetchAll();
        foreach ($profils as &$profil) {
            $profil[ 'roles' ] = self::fileprofil()->getRolesUserByProfil($profil[ 'profil_file_id' ]);
        }

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'title_main' => '<i class="fa fa-folder"></i> ' . t('File profile')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'page-profil.php', $this->pathViews, [
                    'link_add' => self::router()->getRoute('filemanager.profil.create'),
                    'profils'  => $profils,
                    'router'   => self::router()
        ]);
    }

    public function create($req)
    {
        $content = [];
        if (isset($_SESSION[ 'inputs' ])) {
            $content = $_SESSION[ 'inputs' ];
            unset($_SESSION[ 'inputs' ]);
        }

        $action = self::router()->getRoute('filemanager.profil.store');
        $form   = (new FormPermission([ 'method' => 'post', 'action' => $action ]))
            ->content($content)
            ->roles(self::query()->from('role')->where('role_id', '>', 1)->fetchAll(), [])
            ->createForm();

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
                    'title_main' => '<i class="fa fa-user" aria-hidden="true"></i> ' . t('Add a files profile')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'page-form.php', $this->pathViews, [
                    'form' => $form
        ]);
    }

    public function store($req)
    {
        $validator = $this->getValidator($req);

        $validatorExtension = new Validator();
        $isValid            = $validator->isValid();
        $listExtension      = implode(',', FileManager::getWhiteList());
        foreach ($validator->getInput('file_extensions', []) as $key => $extension) {
            $validatorExtension
                ->addRule($key, 'inarray:' . $listExtension)
                ->addLabel($key, $extension)
                ->addInput($key, $key);
        }
        $isValid &= $validatorExtension->isValid();

        if ($isValid) {
            $data               = $this->getData($validator);
            self::query()
                ->insertInto('profil_file', array_keys($data))
                ->values($data)
                ->execute();
            $id_permission_file = self::schema()->getIncrement('profil_file');
            $this->storeProfilRole($validator, $id_permission_file);

            $_SESSION[ 'messages' ][ 'success' ] = [ t('Saved configuration') ];
            $route                               = self::router()->getRoute('filemanager.profil.admin');

            return new Redirect($route);
        }

        $_SESSION[ 'inputs' ]               = $validator->getInputs();
        $_SESSION[ 'messages' ][ 'errors' ] = $validator->getErrors();
        $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();

        $route = self::router()->getRoute('filemanager.profil.create');

        return new Redirect($route);
    }

    public function edit($id, $req)
    {
        if (!($content = self::fileprofil()->find($id))) {
            return $this->get404($req);
        }
        $content[ 'file_extensions' ] = explode(',', $content[ 'file_extensions' ]);

        if (isset($_SESSION[ 'inputs' ])) {
            $content = array_merge($content, $_SESSION[ 'inputs' ]);
            unset($_SESSION[ 'inputs' ]);
        }

        $action = self::router()->getRoute('filemanager.profil.update', [ ':id' => $id ]);
        $form   = (new FormPermission([ 'method' => 'post', 'action' => $action ]))
            ->roles(self::query()->from('role')->where('role_id', '>', 1)->fetchAll(), self::fileprofil()->getIdRolesUser($id))
            ->content($content)
            ->createForm();

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
                    'title_main' => '<i class="fa fa-user" aria-hidden="true"></i> ' . t('Edit the file profile')
                ])
                ->view('page.messages', $messages)
                ->make('page.content', 'page-form.php', $this->pathViews, [
                    'form' => $form
        ]);
    }

    public function update($id, $req)
    {
        if (!($content = self::fileprofil()->find($id))) {
            return $this->get404($req);
        }

        $validator = $this->getValidator($req);

        $validatorExtension = new Validator();
        $isValid            = $validator->isValid();
        $listExtension      = implode(',', FileManager::getWhiteList());
        foreach ($validator->getInput('file_extensions', []) as $key => $extension) {
            $validatorExtension
                ->addRule($key, 'inarray:' . $listExtension)
                ->addLabel($key, $extension)
                ->addInput($key, $key);
        }
        $isValid &= $validatorExtension->isValid();

        if ($isValid) {
            $data = $this->getData($validator);
            self::query()
                ->update('profil_file', $data)
                ->where('profil_file_id', '==', $id)
                ->execute();
            $this->updateProfilRole($validator, $id);

            $_SESSION[ 'messages' ][ 'success' ] = [ t('Saved configuration') ];
            $route                               = self::router()->getRoute('filemanager.profil.admin');

            return new Redirect($route);
        }

        $_SESSION[ 'inputs' ]               = $validator->getInputs();
        $_SESSION[ 'messages' ][ 'errors' ] = $validator->getErrors();
        $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();

        $route = self::router()->getRoute('filemanager.profil.edit', [ ':id' => $id ]);

        return new Redirect($route);
    }

    public function remove($id, $req)
    {
        if (!self::fileprofil()->find($id)) {
            $this->get404();
        }

        $form = (new FormBuilder([
            'action' => self::router()->getRoute('filemanager.profil.delete', [
                ':id' => $id
            ]),
            'method' => 'post',
            ]))
            ->group('folder-fieldset', 'fieldset', function ($form) {
                $form->legend('folder-legend', t('Delete file profile'))
                ->html('folder-message', '<p:css:attr>:_content</p>', [
                    '_content' => t('Warning ! The deletion of the file profile is final.')
                ]);
            })
            ->token('token_file_permission')
            ->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ]);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'title_main' => '<i class="fa fa-user" aria-hidden="true"></i> ' . t('Delete file profile')
                ])
                ->make('page.content', 'page-form.php', $this->pathViews, [
                    'form' => $form
        ]);
    }

    public function delete($id, $req)
    {
        if (!self::fileprofil()->find($id)) {
            $this->get404();
        }

        self::query()
            ->from('profil_file')
            ->delete()
            ->where('profil_file_id', '==', $id)
            ->execute();

        $route = self::router()->getRoute('filemanager.profil.admin', [ ':id' => $id ]);

        return new Redirect($route);
    }

    protected function storeProfilRole($validator, $profil_file_id)
    {
        self::query()->insertInto('profil_file_role', [
            'profil_file_id', 'role_id'
        ]);
        foreach (array_keys($validator->getInput('roles')) as $role_id) {
            self::query()->values([ $profil_file_id, $role_id ]);
        }
        self::query()->execute();
    }

    protected function updateProfilRole($validator, $profil_file_id)
    {
        self::query()
            ->from('profil_file_role')
            ->where('profil_file_id', '==', $profil_file_id)
            ->delete()->execute();
        self::query()
            ->insertInto('profil_file_role', [ 'profil_file_id', 'role_id' ]);
        foreach (array_keys($validator->getInput('roles', [])) as $role_id) {
            self::query()->values([ $profil_file_id, $role_id ]);
        }
        self::query()->execute();
    }

    protected function getValidator($req)
    {
        $validator = (new Validator())
            ->setRules([
                'folder_show'           => 'required|string',
                'folder_show_sub'       => 'bool',
                'profil_weight'         => 'int|between:0,50',
                'roles'                 => '!required|array',
                'folder_store'          => 'bool',
                'folder_update'         => 'bool',
                'folder_delete'         => 'bool',
                'folder_size'           => '!required|int|min:0',
                'file_store'            => 'bool',
                'file_update'           => 'bool',
                'file_delete'           => 'bool',
                'file_download'         => 'bool',
                'file_clipboard'        => 'bool',
                'file_size'             => '!required|int|min:0',
                'file_extensions_all'   => 'bool',
                'file_extensions'       => '!required|array',
                'token_file_permission' => 'token'
            ])
            ->setLabel([
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
            'file_size'           => (int) $validator->getInput('file_size'),
            'file_extensions_all' => (bool) $validator->getInput('file_extensions_all'),
            'file_extensions'     => (bool) !$validator->getInput('file_extensions_all')
            ? implode(',', $validator->getInput('file_extensions', []))
            : ''
        ];
    }
}
