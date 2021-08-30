<?php

declare(strict_types=1);

namespace SoosyzeCore\Menu\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Util\Util;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\Menu\Form\FormMenu;

class Menu extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/services.php';
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routes.php';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function create(ServerRequestInterface $req): ResponseInterface
    {
        $values = [];
        $this->container->callHook('menu.create.form.data', [ &$values ]);

        $action = self::router()->getRoute('menu.store');

        $form = (new FormMenu([ 'action' => $action, 'method' => 'post' ]))
            ->setValues($values)
            ->makeFields();

        $this->container->callHook('menu.create.form', [ &$form, $values ]);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-bars" aria-hidden="true"></i>',
                    'title_main' => t('Add a menu')
                ])
                ->make('page.content', 'menu/content-menu-form.php', $this->pathViews, [
                    'form' => $form
        ]);
    }

    public function store(ServerRequestInterface $req): ResponseInterface
    {
        $validator = $this->getValidator($req);

        $this->container->callHook('menu.store.validator', [ &$validator ]);

        if ($validator->isValid()) {
            $data = $this->getData($validator);

            $this->container->callHook('menu.store.before', [ $validator, &$data ]);
            self::query()
                ->insertInto('menu', array_keys($data))
                ->values($data)
                ->execute();
            $this->container->callHook('menu.store.after', [ $validator ]);

            $_SESSION[ 'messages' ][ 'success' ][] = t('Saved configuration');

            return $this->json(201, [
                    'redirect' => self::router()->getRoute('menu.show', [ ':menu' => $data[ 'name' ] ])
            ]);
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }

    public function edit(string $nameMenu, ServerRequestInterface $req): ResponseInterface
    {
        if (!($values = self::menu()->getMenu($nameMenu)->fetch())) {
            return $this->get404($req);
        }

        $this->container->callHook('menu.store.form.data', [ &$values ]);

        $action = self::router()->getRoute('menu.update', [ ':menu' => $nameMenu ]);

        $form = (new FormMenu(['action' => $action, 'method' => 'put' ]))
            ->setValues($values)
            ->makeFields();

        $this->container->callHook('menu.store.form', [ &$form, $values ]);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-bars" aria-hidden="true"></i>',
                    'title_main' => t('Edit the menu :name', [
                        ':name' => t($values[ 'title' ])
                    ])
                ])
                ->view('page.submenu', self::menu()->getMenuSubmenu('menu.edit', $nameMenu))
                ->make('page.content', 'menu/content-menu-form.php', $this->pathViews, [
                    'form' => $form
                ]);
    }

    public function update(string $nameMenu, ServerRequestInterface $req): ResponseInterface
    {
        if (!self::menu()->getMenu($nameMenu)->fetch()) {
            return $this->json(404, [
                    'messages' => [ 'errors' => t('The requested resource does not exist.') ]
            ]);
        }

        $validator = $this->getValidator($req);

        $this->container->callHook('menu.update.validator', [ &$validator ]);

        if ($validator->isValid()) {
            $data = $this->getData($validator, $nameMenu);

            $this->container->callHook('menu.update.before', [ $validator, &$data ]);
            self::query()
                ->update('menu', $data)
                ->where('name', '=', $nameMenu)
                ->execute();
            $this->container->callHook('menu.update.after', [ $validator ]);

            $_SESSION[ 'messages' ][ 'success' ][] = t('Saved configuration');

            return $this->json(200, [
                    'redirect' => self::router()->getRoute('menu.show', [ ':menu' => $nameMenu ])
            ]);
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }

    public function remove(string $nameMenu, ServerRequestInterface $req): ResponseInterface
    {
        if (!($values = self::menu()->getMenu($nameMenu)->fetch())) {
            return $this->get404($req);
        }

        $this->container->callHook('menu.remove.form.data', [ &$values, $nameMenu ]);

        $action = self::router()->getRoute('menu.delete', [ ':menu' => $nameMenu ]);

        $form = (new FormBuilder([ 'action' => $action, 'class' => 'form-api', 'method' => 'delete' ]))
            ->group('menu-fieldset', 'fieldset', function ($form) {
                $form->legend('menu-legend', t('Menu deletion'))
                ->group('info-group', 'div', function ($form) {
                    $form->html('info', '<p:attr>:content</p>', [
                        ':content' => t('Warning ! The deletion of the menu is final.')
                    ]);
                }, [ 'class' => 'alert alert-warning' ]);
            })
            ->token('token_menu_remove')
            ->submit('submit', t('Delete'), [ 'class' => 'btn btn-danger' ])
            ->html('cancel', '<button:attr>:content</button>', [
                ':content' => t('Cancel'),
                'class'    => 'btn btn-default',
                'onclick'  => 'javascript:history.back();',
                'type'     => 'button'
            ]);

        $this->container->callHook('menu.remove.form', [ &$form, $values, $nameMenu ]);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-bars" aria-hidden="true"></i>',
                    'title_main' => t('Delete the menu :name', [
                        ':name' => t($values[ 'title' ])
                    ])
                ])
                ->view('page.submenu', self::menu()->getMenuSubmenu('menu.remove', $nameMenu))
                ->make('page.content', 'menu/content-menu-form.php', $this->pathViews, [
                    'form' => $form
                ]);
    }

    public function delete(string $nameMenu, ServerRequestInterface $req): ResponseInterface
    {
        if (!self::menu()->getMenu($nameMenu)->fetch()) {
            return $this->json(404, [
                    'messages' => [ 'errors' => t('The requested resource does not exist.') ]
            ]);
        }

        $validator = (new Validator())
            ->setRules([
                'name' => 'required|string|max:255',
            ])
            ->setInputs([ 'name' => $nameMenu ]);

        $this->container->callHook('menu.delete.validator', [ &$validator, $nameMenu ]);

        if ($validator->isValid()) {
            $this->container->callHook('menu.delete.before', [ $validator, $nameMenu ]);

            self::query()
                ->from('menu_link')
                ->delete()
                ->where('menu', '=', $nameMenu)
                ->execute();
            self::query()
                ->from('menu')
                ->delete()
                ->where('name', '=', $nameMenu)
                ->execute();
            $this->container->callHook('menu.delete.after', [ $validator, $nameMenu ]);

            $_SESSION[ 'messages' ][ 'success' ][] = t('Saved configuration');

            return $this->json(200, [
                    'redirect' => self::router()->getRoute('menu.admin')
            ]);
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }

    private function getValidator(ServerRequestInterface $req): Validator
    {
        return (new Validator())
                ->setRules([
                    'description' => 'required|string|max:255',
                    'title'       => 'required|string|max:255|!equal:create'
                ])
                ->setLabels([
                    'description' => t('Description'),
                    'title'       => t('Menu title')
                ])
                ->setInputs($req->getParsedBody());
    }

    private function getData(Validator $validator, ?string $nameMenu = null): array
    {
        $data = [
            'description' => $validator->getInput('description'),
            'title'       => $validator->getInput('title')
        ];

        if ($nameMenu === null) {
            $data[ 'name' ] = Util::strSlug($validator->getInput('title'), '-');
        }

        return $data;
    }
}
