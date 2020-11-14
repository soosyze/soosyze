<?php

namespace SoosyzeCore\Menu\Services;

class HookBlock
{
    protected $menu;
    
    /**
     * @var \Soosyze\Components\Router\Router
     */
    protected $router;

    public function __construct($menu, $router)
    {
        $this->menu   = $menu;
        $this->router = $router;
    }

    public function hookCreateFormData(array &$blocks)
    {
        $menus = $this->menu->getAllMenu();
        
        foreach ($menus as $menu) {
            $blocks[ "menu.{$menu[ 'name' ]}" ] = [
                'hook'      => 'menu',
                'key_block' => "menu.{$menu[ 'name' ]}",
                'options'   => [ 'name' => $menu[ 'name' ], 'parent' => -1, 'level' => 0 ],
                'path'      => $this->menu->getPathViews(),
                'title'     => $menu[ 'title' ],
                'tpl'       => "components/block/menu-{$menu[ 'name' ]}.php"
            ];
        }
    }

    public function hookBlockMenu($tpl, array $options)
    {
        if ($menu = $this->menu->renderMenu($options[ 'name' ], $options['parent'])) {
            return $menu->setName('components/block/menu.php')
                    ->setNamesOverride([ "components/block/menu-{$options[ 'name' ]}.php" ]);
        }
    }

    public function hookMenuEditFormData(&$form, $data)
    {
        $menus = $this->menu->getAllMenu();

        $options = [];
        foreach ($menus as $menu) {
            $options[] = [
                'value' => $menu[ 'name' ],
                'label' => $menu[ 'title' ],
                'attr'  => [
                    'data-link' => $this->router->getRoute('menu.api.show', [
                        ':menu' => $menu[ 'name' ]
                    ])
                ]
            ];
        }

        $form->group('menu-fieldset', 'fieldset', function ($form) use ($data, $options) {
            $form->legend('menu-legend', t('Settings'))
                ->group('name-group', 'div', function ($form) use ($data, $options) {
                    $form->label('name-label', t('Menu to display'))
                    ->select('name', $options, [
                        'class'       => 'form-control ajax-control',
                        'data-target' => 'select[name="parent"]',
                        'min'         => 1,
                        'max'         => 4,
                        'selected'    => $data[ 'options' ][ 'name' ]
                    ]);
                }, [ 'class' => 'form-group' ])
                ->group('parent-group', 'div', function ($form) use ($data) {
                    $form->label('parent-label', t('Parent link'), [
                        'data-tooltip' => t('Show child links of the selected one.')
                    ])
                    ->select('parent', $this->menu->renderMenuSelect($data[ 'options' ][ 'name' ]), [
                        'class'    => 'form-control',
                        'selected' => $data[ 'options' ][ 'parent' ]
                    ]);
                }, [ 'class' => 'form-group' ]);
        });
    }

    public function hookMenuUpdateValidator(&$validator, $id)
    {
        $menus = $this->menu->getAllMenu();

        $listName = [];
        foreach ($menus as $menu) {
            $listName[] = $menu[ 'name' ];
        }

        $validator
            ->addRule('name', 'required|inarray:' . implode(',', $listName))
            ->addRule('parent', 'required|numeric')
            ->addLabel('name', t('Menu to display'))
            ->addLabel('parent', t('Menu to display'));
    }

    public function hookMenuUpdateBefore($validator, &$values, $id)
    {
        $values[ 'options' ] = json_encode([
            'name'   => $validator->getInput('name'),
            'parent' => (int) $validator->getInput('parent')
        ]);
    }
}
