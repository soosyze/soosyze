<?php

declare(strict_types=1);

namespace SoosyzeCore\Menu\Hook;

use Soosyze\Components\Form\FormGroupBuilder;
use Soosyze\Components\Router\Router;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\Menu\Services\Menu;
use SoosyzeCore\QueryBuilder\Services\Query;
use SoosyzeCore\System\Services\Modules;
use SoosyzeCore\Template\Services\Block as ServiceBlock;

class Block implements \SoosyzeCore\Block\BlockInterface
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
                'depth'  => 10,
                'name'   => 'menu-main',
                'parent' => -1,
            ],
            'path'      => self::PATH_VIEWS,
            'title'     => 'Menu',
            'tpl'       => 'components/block/menu-menu.php'
        ];
    }

    public function hookMenu(ServiceBlock $tpl, array $options): ServiceBlock
    {
        $menu = $this->menu->renderMenu($options[ 'name' ], $options[ 'parent' ], $options[ 'depth' ]);
        if ($menu !== null) {
            return $menu->setNamesOverride([ "components/block/menu-{$options[ 'name' ]}.php" ]);
        }

        return $tpl;
    }

    public function hookMenuForm(FormGroupBuilder &$form, array $values): void
    {
        $form->group('menu-fieldset', 'fieldset', function ($form) use ($values) {
            $form->legend('menu-legend', t('Settings'))
                ->group('name-group', 'div', function ($form) use ($values) {
                    $form->label('name-label', t('Menu to display'))
                    ->select('name', $this->getOptionsName(), [
                        ':selected'   => $values[ 'options' ][ 'name' ],
                        'class'       => 'form-control ajax-control',
                        'data-target' => 'select[name="parent"]',
                        'max'         => 4,
                        'min'         => 1
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('parent-group', 'div', function ($form) use ($values) {
                    $form->label('parent-label', t('Parent link'), [
                        'data-tooltip' => t('Show child links of the selected one.')
                    ])
                    ->select('parent', $this->menu->renderMenuSelect($values[ 'options' ][ 'name' ]), [
                        ':selected' => $values[ 'options' ][ 'parent' ],
                        'class'     => 'form-control',
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('depth-group', 'div', function ($form) use ($values) {
                    $form->label('depth-label', t('Menu depth'), [
                        'data-tooltip' => t('Nombre de sous menu à afficher')
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
        $names = array_column($menus, 'name');

        $validator
            ->addRule('depth', 'required|numeric|between_numeric:0,10')
            ->addRule('name', 'required|inarray:' . implode(',', $names))
            ->addRule('parent', 'required|numeric');
        $validator
            ->addLabel('depth', t('Menu depth'))
            ->addLabel('name', t('Menu to display'))
            ->addLabel('parent', t('Parent link'));
    }

    public function hookMenuBefore(Validator $validator, array &$data): void
    {
        $data[ 'options' ] = json_encode([
            'depth'  => (int) $validator->getInput('depth'),
            'name'   => $validator->getInput('name'),
            'parent' => (int) $validator->getInput('parent'),
        ]);
    }

    public function hookMenuRemoveForm(
        FormGroupBuilder &$form,
        array $values,
        string $nameMenu
    ): void {
        if (!$this->modules->has('Block')) {
            return;
        }

        $isBlock = $this->query
            ->from('block')
            ->where('key_block', '=', 'menu')
            ->where('options', 'like', '%' . $nameMenu . '%')
            ->fetch();

        if ($isBlock === []) {
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

    public function hookMenuDeleteBefore(Validator $validator, string $nameMenu): void
    {
        if (!($this->modules->has('Block') && $validator->getInput('delete_block'))) {
            return;
        }

        $this->query
            ->from('block')
            ->delete()
            ->where('key_block', '=', 'menu')
            ->where('options', 'like', '%' . $nameMenu . '%')
            ->execute();
    }

    private function getOptionsName(): array
    {
        $menus = $this->menu->getAllMenu();

        $options = [];
        foreach ($menus as $menu) {
            $options[] = [
                'attr'  => [
                    'data-link' => $this->router->generateUrl('menu.api.show', [
                        ':menu' => $menu[ 'name' ]
                    ])
                ],
                'label' => t($menu[ 'title' ]),
                'value' => $menu[ 'name' ]
            ];
        }

        return $options;
    }
}
