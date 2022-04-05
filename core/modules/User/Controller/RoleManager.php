<?php

declare(strict_types=1);

namespace SoosyzeCore\User\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Validator\Validator;

/**
 * @method \SoosyzeCore\QueryBuilder\Services\Query  query()
 * @method \SoosyzeCore\Template\Services\Templating template()
 * @method \SoosyzeCore\User\Services\User           user()
 *
 * @phpstan-import-type RoleEntity from \SoosyzeCore\User\Extend
 */
class RoleManager extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function admin(ServerRequestInterface $req): ResponseInterface
    {
        /** @phpstan-var array<RoleEntity> $values */
        $values = self::query()->from('role')->orderBy('role_weight')->fetchAll();

        $this->container->callHook('user.role.admin.form.data', [ &$values ]);

        $form = new FormBuilder([
            'action' => self::router()->generateUrl('user.role.admin.check'),
            'class'  => 'form-api',
            'method' => 'patch'
        ]);

        foreach ($values as &$role) {
            $role[ 'link_edit' ] = self::router()->generateUrl('user.role.edit', [
                ':id' => $role[ 'role_id' ]
            ]);
            if ($role[ 'role_id' ] > 3) {
                $role[ 'link_remove' ] = self::router()->generateUrl('user.role.remove', [
                    ':id' => $role[ 'role_id' ]
                ]);
            }
            $form->group("role_{$role[ 'role_id' ]}-group", 'div', function ($form) use ($role) {
                $form->group('role_weight-flex', 'div', function ($form) use ($role) {
                    $form->number("role_weight-{$role[ 'role_id' ]}", [
                        ':actions' => 1,
                        'class'    => 'form-control',
                        'max'      => 50,
                        'min'      => 1,
                        'value'    => $role[ 'role_weight' ]
                    ]);
                }, [ 'class' => 'form-group-flex' ]);
            });
        }
        unset($role);

        $form
            ->group('submit-group', 'div', function ($form) {
                $form->token('token_role_form')
                ->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ]);
            });

        $this->container->callHook('user.role.admin.form', [ &$form, $values ]);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'icon'       => '<i class="fa fa-user-tag" aria-hidden="true"></i>',
                    'title_main' => t('Administer roles')
                ])
                ->view('page.submenu', self::user()->getUserManagerSubmenu('user.role.admin'))
                ->make('page.content', 'user/content-role_manager-admin.php', $this->pathViews, [
                    'form'     => $form,
                    'link_add' => self::router()->generateUrl('user.role.create'),
                    'roles'    => $values
                ]);
    }

    public function adminCheck(ServerRequestInterface $req): ResponseInterface
    {
        /** @phpstan-var array<RoleEntity> $roles */
        $roles = self::query()->from('role')->fetchAll();

        $validator = (new Validator())
            ->addRule('token_role_form', 'token')
            ->setInputs((array) $req->getParsedBody());

        foreach ($roles as $role) {
            $validator
                ->addRule("role_weight-{$role[ 'role_id' ]}", 'required|between_numeric:1,50')
                ->addLabel("role_weight-{$role[ 'role_id' ]}", t($role[ 'role_label' ]));
        }

        $this->container->callHook('user.role.admin.check.validator', [ &$validator ]);

        if ($validator->isValid()) {
            foreach ($roles as $role) {
                $data = [
                    'role_weight' => $validator->getInputInt("role_weight-{$role[ 'role_id' ]}")
                ];

                $this->container->callHook('user.role.admin.check.before', [
                    &$validator, &$data
                ]);

                self::query()
                    ->update('role', $data)
                    ->where('role_id', '=', $role[ 'role_id' ])
                    ->execute();

                $this->container->callHook('user.role.admin.check.after', [ &$validator ]);
            }

            $_SESSION[ 'messages' ][ 'success' ][] = t('Saved configuration');

            return $this->json(200, [
                    'redirect' => self::router()->generateUrl('user.role.admin')
            ]);
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }
}
