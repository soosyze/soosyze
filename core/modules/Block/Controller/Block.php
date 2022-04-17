<?php

declare(strict_types=1);

namespace SoosyzeCore\Block\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\Block\Form\FormBlock;
use SoosyzeCore\Block\Form\FormDeleteBlock;
use SoosyzeCore\Block\Form\FormListBlock;
use SoosyzeCore\Template\Services\Block as ServiceBlock;

/**
 * @method \SoosyzeCore\Block\Services\Block         block()
 * @method \SoosyzeCore\QueryBuilder\Services\Query  query()
 * @method \SoosyzeCore\Template\Services\Templating template()
 * @method \SoosyzeCore\User\Services\User           user()
 * @method \SoosyzeCore\Filter\Services\Xss          xss()
 *
 * @phpstan-import-type BlockEntity from \SoosyzeCore\Block\Extend
 */
class Block extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathServices = dirname(__DIR__) . '/Config/services.php';
        $this->pathRoutes   = dirname(__DIR__) . '/Config/routes.php';
        $this->pathViews    = dirname(__DIR__) . '/Views/';
    }

    public function createList(string $theme, string $section): ServiceBlock
    {
        $blocks = self::block()->getBlocks();

        foreach ($blocks as $key => &$block) {
            $block[ 'link_show_create' ] = self::router()->generateUrl('block.create.show', [
                'theme' => $theme,
                'id'    => $key
            ]);
        }

        $action = self::router()->generateUrl('block.create.form', [
            'theme'   => $theme
        ]);

        $form = (new FormListBlock([ 'action' => $action, 'method' => 'post' ]))
            ->setValues([
                'blocks'  => $blocks,
                'section' => $section
            ])
            ->makeFields();

        $this->container->callHook('block.create.form', [ &$form, $blocks ]);

        return self::template()
                ->getTheme('theme_admin')
                ->createBlock('block/modal-form-create_list.php', $this->pathViews)
                ->addVars([
                    'class' => 'form-create_list',
                    'form'  => $form,
                    'title' => t('Add a block')
        ]);
    }

    /**
     * @return ServiceBlock|ResponseInterface
     */
    public function createShow(string $id)
    {
        $block = self::block()->getBlock($id);

        if (!$block) {
            return $this->get404();
        }

        if (empty($block[ 'hook' ])) {
            $srcImage = self::core()->getPath('modules', 'modules/core', false) . '/Block/Assets/misc/static.svg';

            $block[ 'content' ] = self::template()
                ->getTheme('theme_admin')
                ->createBlock($block[ 'tpl' ], $block[ 'path' ])
                ->addVar('src_image', $srcImage);
        } else {
            $tpl = self::template()
                ->getTheme('theme_admin')
                ->createBlock($block[ 'tpl' ], $block[ 'path' ]);

            $block[ 'content' ] = (string) $this->container->callHook(
                'block.' . $block[ 'hook' ],
                [ $tpl, $block[ 'options' ] ?? null ]
            );
        }

        return self::template()
                ->getTheme('theme_admin')
                ->createBlock('block/block-create_show.php', $this->pathViews)
                ->addVar('block', $block);
    }

    /**
     * @return ServiceBlock|ResponseInterface
     */
    public function createForm(
        string $theme,
        ServerRequestInterface $req
    ) {
        /** @var array $body */
        $body   = $req->getParsedBody();
        $key    = $body[ 'key_block' ] ?? null;
        $values = self::block()->getBlock($key);

        if ($values === null) {
            return $this->get404();
        }

        $values[ 'key_block' ] = $key;
        $values[ 'section' ]   = $body[ 'section' ] ?? null;
        $values[ 'theme' ]     = $theme;

        if (empty($values[ 'hook' ])) {
            $srcImage = self::core()->getPath('modules', 'modules/core', false) . '/Block/Assets/misc/static.svg';

            $values[ 'content' ] = self::template()
                ->getTheme('theme_admin')
                ->createBlock($values[ 'tpl' ], $values[ 'path' ])
                ->addVar('src_image', $srcImage);
        }

        $this->container->callHook('block.create.form.data', [ &$values, $theme ]);

        $action = self::router()->generateUrl('block.store', [
            'theme'   => $theme
        ]);

        $form = (new FormBlock([
                'action'        => $action,
                'class'         => 'form-api',
                'data-tab-pane' => '.pane-block',
                'method'        => 'post'
                ])
            )
            ->setValues($values)
            ->setRoles(self::user()->getRoles())
            ->makeFields();

        if (!empty($values[ 'hook' ])) {
            $form->append('block-fieldset', function ($form) use ($values) {
                self::core()->callHook("block.{$values[ 'hook' ]}.create.form", [
                    &$form, $values
                ]);
            });
        }

        $this->container->callHook('block.create.form', [ &$form, $values, $theme ]);

        return self::template()
                ->getTheme('theme_admin')
                ->createBlock('block/modal-form.php', $this->pathViews)
                ->addVars([
                    'class'            => 'form-create',
                    'fieldset_submenu' => self::block()->getBlockFieldsetSubmenu(),
                    'form'             => $form,
                    'section'          => $values[ 'section' ],
                    'title'            => t('Add block :title', [ ':title' => $values[ 'title' ] ])
        ]);
    }

    public function store(
        string $theme,
        ServerRequestInterface $req
    ): ResponseInterface {
        $validator = $this->getValidator($req, $theme);

        $this->container->callHook('block.store.validator', [ &$validator, $theme ]);

        $isValid = $validator->isValid();

        if ($isValid) {
            $block = self::block()->getBlock($validator->getInputString('key_block'));

            $hook = $block[ 'hook' ] ?? null;

            if ($hook) {
                $this->container->callHook("block.{$hook}.store.validator", [
                    &$validator, $theme
                ]);
            }

            /* Ajoute à la validation générale la validation des rôles. */
            $isValid = $validator->isValid();
        }

        if (!$validator->hasError('roles')) {
            $validatorRole = $this->getValidatorRoles($validator->getInputArray('roles'));

            /* Ajoute à la validation générale la validation des rôles. */
            $isValid &= $validatorRole->isValid();
        }

        if ($isValid) {
            $data = $this->getData($validator);
            $data += [
                'hook'    => $block[ 'hook' ] ?? null,
                'options' => json_encode($block[ 'options' ] ?? [])
            ];

            if (!empty($block[ 'hook' ])) {
                $this->container->callHook("block.{$block[ 'hook' ]}.store.before", [
                    $validator, &$data, $theme
                ]);
            }
            $this->container->callHook('block.store.before', [ $validator, &$data, $theme ]);

            self::query()
                ->insertInto('block', array_keys($data))
                ->values($data)
                ->execute();

            if (!empty($block[ 'hook' ])) {
                $this->container->callHook("block.{$block[ 'hook' ]}.store.after", [
                    &$validator
                ]);
            }
            $this->container->callHook('block.store.after', [ $validator, $data, $theme  ]);

            return $this->json(201, [
                    'redirect' => self::router()->generateUrl('block.section.admin', [
                        'theme'   => $theme
                    ])
            ]);
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }

    /**
     * @return ServiceBlock|ResponseInterface
     */
    public function edit(
        string $theme,
        int $id,
        ServerRequestInterface $req
    ) {
        if (!($values = $this->find($id))) {
            return $this->get404($req);
        }

        $values[ 'roles' ]   = explode(',', $values[ 'roles' ]);

        if (!empty($values[ 'key_block' ])) {
            $values[ 'options' ] = array_merge(
                self::block()->getBlock($values[ 'key_block' ])[ 'options' ] ?? [],
                self::block()->decodeOptions($values[ 'options' ])
            );
        }

        $this->container->callHook('block.edit.form.data', [ &$values, $theme, $id ]);

        $action = self::router()->generateUrl('block.update', [
            'theme'   => $theme,
            'id'      => $values[ 'block_id' ]
        ]);

        $form = (new FormBlock([
                'action'        => $action,
                'class'         => 'form-api',
                'data-tab-pane' => '.pane-block',
                'method'        => 'put'
                ])
            )
            ->setValues($values)
            ->setRoles(self::user()->getRoles())
            ->makeFields();

        if (!empty($values[ 'hook' ])) {
            $form->append('block-fieldset', function ($form) use ($values, $id) {
                self::core()->callHook("block.{$values[ 'hook' ]}.edit.form", [
                    &$form, $values, $id
                ]);
            });
        }

        $this->container->callHook('block.edit.form', [ &$form, $values, $theme, $id ]);

        return self::template()
                ->getTheme('theme_admin')
                ->createBlock('block/modal-form.php', $this->pathViews)
                ->addVars([
                    'class'            => 'form-edit',
                    'fieldset_submenu' => self::block()->getBlockFieldsetSubmenu(),
                    'form'             => $form,
                    'menu'             => self::block()->getBlockSubmenu('block.edit', $theme, $id),
                    'title'            => t('Edit block :title', [
                        ':title' => self::xss()->getKses()->filter($values[ 'title' ])
                    ])
        ]);
    }

    public function update(string $theme, int $id, ServerRequestInterface $req): ResponseInterface
    {
        if (!($block = $this->find($id))) {
            return $this->json(404, [
                    'messages' => [ 'errors' => [ t('The requested resource does not exist.') ] ]
            ]);
        }

        $validator = $this->getValidator($req, $theme, $id);

        if (!empty($block[ 'hook' ])) {
            $this->container->callHook("block.{$block[ 'hook' ]}.update.validator", [
                &$validator, $theme, $id
            ]);
        }

        $this->container->callHook('block.update.validator', [ &$validator, $theme, $id ]);

        $isValid = $validator->isValid();

        if (!$validator->hasError('roles')) {
            $validatorRole = $this->getValidatorRoles($validator->getInputArray('roles'));

            /* Ajoute à la validation générale la validation des rôles. */
            $isValid &= $validatorRole->isValid();
        }

        if ($isValid) {
            $data = $this->getData($validator);

            if (!empty($block[ 'hook' ])) {
                $this->container->callHook("block.{$block[ 'hook' ]}.update.before", [
                    $validator, &$data, $theme, $id
                ]);
            }
            $this->container->callHook('block.update.before', [
                $validator, &$data, $theme, $id
            ]);

            self::query()
                ->update('block', $data)
                ->where('block_id', '=', $id)
                ->execute();

            if (!empty($block[ 'hook' ])) {
                $this->container->callHook("block.{$block[ 'hook' ]}.update.after", [
                    $validator, $data, $theme, $id
                ]);
            }
            $this->container->callHook('block.update.after', [ $validator, $data, $theme, $id ]);

            return $this->json(200, [
                    'redirect'    => self::router()->generateUrl('block.section.admin', [
                        'theme'   => $theme
                    ])
            ]);
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }

    /**
     * @return ServiceBlock|ResponseInterface
     */
    public function remove(string $theme, int $id, ServerRequestInterface $req)
    {
        if (!($values = $this->find($id))) {
            return $this->get404($req);
        }

        $values[ 'roles' ]   = explode(',', $values[ 'roles' ]);
        $values[ 'options' ] = self::block()->decodeOptions($values[ 'options' ]);

        $this->container->callHook('block.remove.form.data', [ &$values, $theme, $id ]);

        $action = self::router()->generateUrl('block.delete', [
            'theme'   => $theme,
            'id'      => $values[ 'block_id' ]
        ]);

        $form = (new FormDeleteBlock([
                'action' => $action,
                'class'  => 'form-api',
                'method' => 'delete'
                ])
            )
            ->makeFields();

        $this->container->callHook('block.remove.form', [ &$form, $values, $theme, $id ]);

        return self::template()
                ->getTheme('theme_admin')
                ->createBlock('block/modal-form.php', $this->pathViews)
                ->addVars([
                    'form'      => $form,
                    'link_show' => '',
                    'menu'      => self::block()->getBlockSubmenu('block.remove', $theme, $id),
                    'title'     => t('Delete block :title', [ ':title' => $values[ 'title' ] ])
        ]);
    }

    public function delete(
        string $theme,
        int $id,
        ServerRequestInterface $req
    ): ResponseInterface {
        if (!$this->find($id)) {
            return $this->json(404, [
                    'messages' => [ 'errors' => [ t('The requested resource does not exist.') ] ]
            ]);
        }

        $validator = (new Validator())
            ->addRule('token_block_delete', 'token')
            ->setInputs((array) $req->getParsedBody());

        $this->container->callHook('block.delete.validator', [
            &$validator, $theme, $id
        ]);

        if ($validator->isValid()) {
            $this->container->callHook('block.delete.before', [ $validator, $theme, $id ]);
            self::query()
                ->from('block')
                ->where('block_id', '=', $id)
                ->delete()
                ->execute();
            $this->container->callHook('block.delete.after', [ $validator, $theme, $id ]);

            return $this->json(200, [
                    'redirect' => self::router()->generateUrl('block.section.admin', [
                        'theme' => $theme
                    ])
            ]);
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }

    private function find(int $id): ?array
    {
        return self::query()->from('block')->where('block_id', '=', $id)->fetch();
    }

    private function getValidator(
        ServerRequestInterface $req,
        string $theme,
        ?int $id = null
    ): Validator {
        $blocks  = self::block()->getBlocks();
        $section = self::template()->getTheme($theme)->getSections();

        $rules = [
            'class'            => '!required|string|max:255',
            'content'          => '!required|string|max:5000',
            'is_title'         => 'bool',
            'key_block'        => '!required|string|inarray:' . implode(',', array_keys($blocks)),
            'pages'            => '!required|string',
            'roles'            => '!required|array',
            'section'          => 'required|inarray:' . implode(',', $section),
            'theme'            => 'required|inarray:public,admin',
            'title'            => 'required|string|max:255',
            'visibility_pages' => 'bool',
            'visibility_roles' => 'bool',
            'weight'           => 'required|numeric|between_numeric:0,50'
        ];

        if ($id === null) {
            $rules[ 'token_block_create' ] = 'token';
        } else {
            $rules[ "token_block_edit_$id" ] = 'token';
        }

        return (new Validator())
                ->setRules($rules)
                ->setLabels([
                    'class'     => t('Class'),
                    'content'   => t('Content'),
                    'is_title'  => t('Afficher le titre'),
                    'key_block' => t('Clé du bloc'),
                    'pages'     => t('List of pages'),
                    'roles'     => t('User Roles'),
                    'section'   => t('Section'),
                    'theme'     => t('Theme'),
                    'title'     => t('Title'),
                    'weight'    => t('Weight')
                ])
                ->setInputs(
                    (array) $req->getParsedBody()
                )
                ->setAttributs([
                    'key_block' => [
                        'inarray' => [
                            ':list' => static function (string $label) use ($blocks): string {
                                return implode(', ', array_column($blocks, 'title'));
                            }
                        ]
                    ],
                    'section' => [
                        'inarray' => [
                            ':list' => static function (string $label) use ($section): string {
                                return implode(', ', $section);
                            }
                        ]
                    ]
                ])
        ;
    }

    private function getValidatorRoles(array $roles = []): Validator
    {
        $validatorRoles = new Validator();

        $listRoles = implode(
            ',',
            array_column(self::user()->getRoles(), 'role_id')
        );

        foreach ($roles as $key => $role) {
            $validatorRoles
                ->addRule("$key-role", 'int|inarray:' . $listRoles)
                ->addLabel("$key-role", t($role))
                ->addInput("$key-role", $key);
        }

        $this->container->callHook('block.update.role.validator', [ &$validatorRoles ]);

        return $validatorRoles;
    }

    private function getData(Validator $validator): array
    {
        return [
            'class'            => $validator->getInput('class'),
            'content'          => $validator->getInput('content'),
            'is_title'         => (bool) $validator->getInput('is_title'),
            'key_block'        => $validator->getInput('key_block'),
            'pages'            => $validator->getInput('pages'),
            'roles'            => implode(',', array_keys($validator->getInputArray('roles'))),
            'section'          => $validator->getInput('section'),
            'theme'            => $validator->getInput('theme'),
            'title'            => $validator->getInput('title'),
            'visibility_pages' => (bool) $validator->getInput('visibility_pages'),
            'visibility_roles' => (bool) $validator->getInput('visibility_roles'),
            'weight'           => $validator->getInputInt('weight')
        ];
    }
}
