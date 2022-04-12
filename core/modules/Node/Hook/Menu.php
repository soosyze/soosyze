<?php

declare(strict_types=1);

namespace SoosyzeCore\Node\Hook;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\Menu\Enum\Menu as EnumMenu;
use SoosyzeCore\QueryBuilder\Services\Query;
use SoosyzeCore\QueryBuilder\Services\Schema;
use SoosyzeCore\System\Services\Alias;

/**
 * @phpstan-import-type MenuEntity from \SoosyzeCore\Menu\Extend
 * @phpstan-import-type MenuLinkEntity from \SoosyzeCore\Menu\Extend
 * @phpstan-import-type NodeMenuLinkEntity from \SoosyzeCore\Node\Extend
 */
class Menu
{
    /**
     * @var Alias
     */
    private $alias;

    /**
     * Si le module menu existe.
     *
     * @var bool
     */
    private $isMenu;

    /**
     * @var Query
     */
    private $query;

    /**
     * @var Schema
     */
    private $schema;

    public function __construct(Alias $alias, Query $query, Schema $schema)
    {
        $this->alias  = $alias;
        $this->query  = $query;
        $this->schema = $schema;

        $this->isMenu = $this->schema->hasTable('menu');
    }

    public function hookNodeFieldsetSubmenu(array &$menu): void
    {
        if (!$this->isMenu) {
            return;
        }

        $menu[] = [
            'class'      => '',
            'link'       => '#menu-fieldset',
            'title_link' => t('Menu')
        ];
    }

    public function hookCreateFormData(array &$data): void
    {
        if (!$this->isMenu) {
            return;
        }

        $data[ 'active' ]     = '';
        $data[ 'menu_id' ]    = EnumMenu::MAIN_MENU;
        $data[ 'title_link' ] = '';
    }

    public function hookEditFormData(array &$data, int $idNode): void
    {
        if (!$this->isMenu) {
            return;
        }

        /** @phpstan-var array{
         *      menu_link_id: int,
         *      menu_id: int,
         *      title_link: string
         * }|null $link
         */
        $link = $this->query
            ->select('menu_link_id', 'menu_id', 'title_link')
            ->from('node_menu_link')
            ->leftJoin('menu_link', 'menu_link_id', '=', 'menu_link.link_id')
            ->where('node_id', '=', $idNode)
            ->fetch();

        $data[ 'active' ]     = (bool) ($link[ 'menu_link_id' ] ?? false);
        $data[ 'menu_id' ]    = $link[ 'menu_id' ] ?? EnumMenu::MAIN_MENU;
        $data[ 'title_link' ] = $link[ 'title_link' ] ?? '';
    }

    public function hookCreateForm(FormBuilder $form, array $data): void
    {
        if (!$this->isMenu) {
            return;
        }

        $form->before('submit-group', function ($form) use ($data) {
            $form->group('menu-fieldset', 'fieldset', function ($form) use ($data) {
                $form->legend('menu-legend', t('Menu'))
                    ->group('active-group', 'div', function ($form) use ($data) {
                        $form->checkbox('active', [
                            'checked'      => $data[ 'active' ],
                            'data-dismiss' => 'toogle',
                            'data-target'  => '#menu_toogle',
                        ])
                        ->label('active-label', '<span class="ui"></span> ' . t('Add a link in the menu'), [
                            'for' => 'active'
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('menu_id-group', 'div', function ($form) use ($data) {
                        $form->group('menu_id-group', 'div', function ($form) use ($data) {
                            $form->label('menu_id-label', t('Menu title'))
                            ->select('menu_id', $this->getOptions(), [
                                ':selected' => $data[ 'menu_id' ],
                                'class'     => 'form-control'
                            ]);
                        }, [ 'class' => 'form-group' ])
                        ->group('title_link-group', 'div', function ($form) use ($data) {
                            $form->label('title_link-label', t('Link title'))
                            ->text('title_link', [
                                'class'       => 'form-control',
                                'placeholder' => t('Example: Home'),
                                'value'       => $data[ 'title_link' ]
                            ]);
                        }, [ 'class' => 'form-group' ]);
                    }, [
                        'id'    => 'menu_toogle',
                        'class' => $data[ 'active' ]
                            ? ''
                            : 'hidden'
                ]);
            }, [
                'class' => 'tab-pane fade',
                'id'    => 'menu-fieldset'
            ]);
        });
    }

    public function hookStoreValidator(Validator $validator): void
    {
        if (!$this->isMenu || !$validator->hasInput('active')) {
            return;
        }

        $menus = $this->query->from('menu')->fetchAll();

        $validator
            ->addRule('active', 'bool')
            ->addRule('menu_id', 'required|int|inarray:' . implode(',', array_column($menus, 'menu_id')))
            ->addRule('title_link', 'required|string|max:255');
        $validator
            ->addLabel('active', t('Active'))
            ->addLabel('menu_id', t('Menu title'))
            ->addLabel('title_link', t('Link title'));
        $validator
            ->setAttributs([
                'menu_id' => [
                    'inarray' => [
                        ':list' => static function () use ($menus): string {
                            return implode(', ', array_column($menus, 'title'));
                        }
                    ]
                ],
            ])
        ;
    }

    public function hookStoreValid(Validator $validator): void
    {
        if (!$this->isMenu || !$validator->hasInput('active')) {
            return;
        }

        $id   = $this->schema->getIncrement('node');
        /** @phpstan-var string $link */
        $link = $this->alias->getAlias("node/$id", "node/$id");

        $this->query->insertInto('menu_link', [
                'key', 'title_link', 'link', 'link_router', 'menu_id', 'weight', 'parent',
                'active'
            ])
            ->values([
                'node.show',
                $validator->getInputString('title_link'),
                $link,
                "node/$id",
                $validator->getInputInt('menu_id'),
                1,
                -1,
                $validator->getInput('node_status_id') == 1,
            ])
            ->execute();

        $linkId = $this->schema->getIncrement('menu_link');

        $this->query->insertInto('node_menu_link', [ 'node_id', 'menu_link_id' ])
            ->values([ $id, $linkId ])
            ->execute();
    }

    public function hookUpdateValid(Validator $validator, int $nodeId): void
    {
        if (!$this->isMenu) {
            return;
        }

        /** @phpstan-var NodeMenuLinkEntity|null $nodeMenuLink */
        $nodeMenuLink = $this->query->from('node_menu_link')
            ->where('node_id', '=', $nodeId)
            ->fetch();

        $link = $this->alias->getAlias("node/$nodeId", "node/$nodeId");

        if ($validator->hasInput('active') && $nodeMenuLink) {
            $this->query->update('menu_link', [
                    'active'     => $validator->getInput('node_status_id') == 1,
                    'link'       => $link,
                    'menu_id'    => $validator->getInputInt('menu_id'),
                    'title_link' => $validator->getInputString('title_link')
                ])
                ->where('link_id', '=', $nodeMenuLink[ 'menu_link_id' ])
                ->execute();
        } elseif ($validator->hasInput('active') && !$nodeMenuLink) {
            $this->query->insertInto('menu_link', [
                    'key', 'title_link', 'link', 'link_router', 'menu_id', 'weight',
                    'parent', 'active'
                ])
                ->values([
                    'node.show',
                    $validator->getInputString('title_link'),
                    $link,
                    "node/$nodeId",
                    $validator->getInputInt('menu_id'),
                    1,
                    -1,
                    $validator->getInput('node_status_id') == 1
                ])
                ->execute();

            $linkId = $this->schema->getIncrement('menu_link');

            $this->query->insertInto('node_menu_link', [ 'node_id', 'menu_link_id' ])
                ->values([ $nodeId, $linkId ])
                ->execute();
        } elseif (!$validator->hasInput('active') && $nodeMenuLink) {
            $this->query->from('node_menu_link')
                ->where('node_id', '=', $nodeId)
                ->delete()
                ->execute();

            $this->query->from('menu_link')
                ->where('link_id', '=', $nodeMenuLink[ 'menu_link_id' ])
                ->delete()
                ->execute();
        }
    }

    public function hookDeleteValid(Validator $validator, int $nodeId): void
    {
        if (!$this->isMenu) {
            return;
        }

        /** @phpstan-var NodeMenuLinkEntity|null $nodeMenuLink */
        $nodeMenuLink = $this->query->from('node_menu_link')
            ->where('node_id', '=', $nodeId)
            ->fetch();

        if ($nodeMenuLink === null) {
            return;
        }

        $this->query->from('node_menu_link')
            ->where('node_id', '=', $nodeId)
            ->delete()
            ->execute();

        $this->query->from('menu_link')
            ->where('link_id', '=', $nodeMenuLink[ 'menu_link_id' ])
            ->delete()
            ->execute();
    }

    public function hookLinkDeleteValid(Validator $validator, int $id): void
    {
        $this->query->from('node_menu_link')
            ->where('menu_link_id', '=', $id)
            ->delete()
            ->execute();
    }

    private function getOptions(): array
    {
        /** @phpstan-var array<MenuEntity> $menus */
        $menus = $this->query->from('menu')->fetchAll();

        $options = [];
        foreach ($menus as $menu) {
            $options[] = [
                'label' => t($menu[ 'title' ]),
                'value' => $menu[ 'menu_id' ]
            ];
        }

        return $options;
    }
}
