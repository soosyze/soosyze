<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Menu\Hook;

use Soosyze\Components\Form\FormGroupBuilder;
use Soosyze\Components\Router\Router;
use Soosyze\Components\Validator\Validator;
use Soosyze\Core\Modules\Menu\Enum\Menu as EnumMenu;
use Soosyze\Core\Modules\Menu\Services\Menu;
use Soosyze\Core\Modules\QueryBuilder\Services\Query;
use Soosyze\Core\Modules\System\Services\Modules;
use Soosyze\Core\Modules\Template\Services\Block as ServiceBlock;

class Block implements \Soosyze\Core\Modules\Block\BlockInterface
{
    /**
     * @var string
     */
    private const PATH_VIEWS = __DIR__ . '/../Views/';

    /**
     * @var Menu
     */
    private $menu;

    /**
     * @var Modules
     */
    private $modules;

    /**
     * @var Query
     */
    private $query;

    /**
     * @var Router
     */
    private $router;

    public function __construct(
        Menu $menu,
        Modules $modules,
        Query $query,
        Router $router
    ) {
        $this->menu    = $menu;
        $this->modules = $modules;
        $this->query   = $query;
        $this->router  = $router;
    }

    public function hookBlockCreateFormData(array &$blocks): void
    {
        $blocks[ 'menu' ] = [
            'description' => 'Displays a menu.',
            'hook'      => 'menu',
            'icon'      => 'fas fa-bars',
            'options'     => [
                'depth'   => 10,
                'menu_id' => EnumMenu::MAIN_MENU,
                'parent'  => -1,
            ],
            'path'      => self::PATH_VIEWS,
            'title'     => 'Menu',
            'tpl'       => 'components/block/menu-menu.php'
        ];
    }

    public function hookMenu(ServiceBlock $tpl, array $options): ServiceBlock
    {
        $menu = $this->menu->renderMenu($options[ 'menu_id' ], $options[ 'parent' ], $options[ 'depth' ]);
        if ($menu !== null) {
            return $menu->setNamesOverride([ "components/block/menu-{$options[ 'menu_id' ]}.php" ]);
        }

        return $tpl;
    }

    public function hookMenuForm(FormGroupBuilder &$form, array $values): void
    {
        $form->group('menu-fieldset', 'fieldset', function ($form) use ($values) {
            $form->legend('menu-legend', t('Settings'))
                ->group('menu_id-group', 'div', function ($form) use ($values) {
                    $form->label('menu_id-label', t('Menu to display'))
                    ->select('menu_id', $this->getOptions(), [
                        ':selected'   => $values[ 'options' ][ 'menu_id' ],
                        'class'       => 'form-control ajax-control',
                        'data-target' => 'select[name="parent"]',
                        'required'    => 1
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('parent-group', 'div', function ($form) use ($values) {
                    $form->label('parent-label', t('Parent link'), [
                        'data-tooltip' => t('Show child links of the selected one.')
                    ])
                    ->select('parent', $this->menu->renderMenuSelect($values[ 'options' ][ 'menu_id' ]), [
                        ':selected' => $values[ 'options' ][ 'parent' ],
                        'class'     => 'form-control',
                        'required'  => 1
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('depth-group', 'div', function ($form) use ($values) {
                    $form->label('depth-label', t('Menu depth'), [
                        'data-tooltip' => t('Number of submenus to display')
                    ])
                    ->number('depth', [
                        ':actions' => 1,
                        'class'    => 'form-control',
                        'max'      => 10,
                        'min'      => 0,
                        'required' => 1,
                        'value'    => $values[ 'options' ][ 'depth' ]
                    ]);
                }, [ 'class' => 'form-group' ]);
        });
    }

    public function hookMenuValidator(Validator &$validator): void
    {
        $menus = $this->menu->getAllMenu();

        $validator
            ->addRule('depth', 'required|int|between_numeric:0,10')
            ->addRule('menu_id', 'required|int|inarray:' . implode(',', array_column($menus, 'menu_id')))
            ->addRule('parent', 'required|int');
        $validator
            ->addLabel('depth', t('Menu depth'))
            ->addLabel('menu_id', t('Menu to display'))
            ->addLabel('parent', t('Parent link'));
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

    public function hookMenuBefore(Validator $validator, array &$data): void
    {
        $data[ 'options' ] = json_encode([
            'depth'   => $validator->getInputInt('depth'),
            'menu_id' => $validator->getInputInt('menu_id'),
            'parent'  => $validator->getInputInt('parent'),
        ]);
    }

    public function hookMenuRemoveForm(
        FormGroupBuilder &$form,
        array $values,
        int $menuId
    ): void {
        if (!$this->modules->has('Block')) {
            return;
        }

        $isBlock = $this->query
            ->from('block')
            ->where('key_block', '=', 'menu')
            ->where('options', 'like', '%"menu_id":' . $menuId . '%')
            ->fetch();

        if ($isBlock === null) {
            return;
        }

        $form->after('info-group', function ($form) {
            $form->group('info-block-group', 'div', function ($form) {
                $form->html('info-block', '<p:attr>:content</p>', [
                    ':content' => t('This menu is displayed by a block. By removing this menu, you will remove the block.')
                ]);
            }, [ 'class' => 'alert alert-warning' ]);
        });
    }

    public function hookMenuDeleteBefore(Validator $validator, int $menuId): void
    {
        if (!($this->modules->has('Block') && $validator->getInput('delete_block'))) {
            return;
        }

        $this->query
            ->from('block')
            ->delete()
            ->where('key_block', '=', 'menu')
            ->where('options', 'like', '%"menu_id":' . $menuId . '%')
            ->execute();
    }

    private function getOptions(): array
    {
        $menus = $this->menu->getAllMenu();

        $options = [];
        foreach ($menus as $menu) {
            $options[] = [
                'attr'  => [
                    'data-link' => $this->router->generateUrl('menu.api.show', [
                        'menuId' => $menu[ 'menu_id' ]
                    ])
                ],
                'label' => t($menu[ 'title' ]),
                'value' => $menu[ 'menu_id' ]
            ];
        }

        return $options;
    }
}
