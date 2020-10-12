<?php

namespace SoosyzeCore\Menu\Services;

class HookBlock
{
    protected $menu;

    public function __construct($menu)
    {
        $this->menu = $menu;
    }

    public function hookCreateFormData(array &$blocks)
    {
        $menus = $this->menu->getAllMenu();
        
        foreach ($menus as $menu) {
            $blocks[ "menu.{$menu[ 'name' ]}" ] = [
                'hook'      => 'menu',
                'key_block' => "menu.{$menu[ 'name' ]}",
                'options'   => [ 'name' => $menu[ 'name' ] ],
                'path'      => $this->menu->getPathViews(),
                'title'     => $menu[ 'title' ],
                'tpl'       => "components/block/menu-{$menu[ 'name' ]}.php"
            ];
        }
    }

    public function hookBlockMenu($tpl, array $options)
    {
        if ($menu = $this->menu->renderMenu($options[ 'name' ])) {
            return $menu->setName('components/block/menu.php')
                    ->setNamesOverride([ "components/block/menu-{$options[ 'name' ]}.php" ]);
        }
    }

    public function hookMenuEditFormData(&$form, $data)
    {
        $menus = $this->menu->getAllMenu();

        $options = [];
        foreach ($menus as $menu) {
            $options[] = [ 'value' => $menu[ 'name' ], 'label' => $menu[ 'title' ] ];
        }

        $form->group('menu-fieldset', 'fieldset', function ($form) use ($data, $options) {
            $form->legend('menu-legend', t('Settings'))
                ->group('name-group', 'div', function ($form) use ($data, $options) {
                    $form->label('name-label', t('Menu to display'))
                    ->select('name', $options, [
                        'class'    => 'form-control',
                        'min'      => 1,
                        'max'      => 4,
                        'selected' => $data[ 'options' ][ 'name' ]
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
            ->addLabel('name', t('Menu to display'));
    }

    public function hookMenuUpdateBefore($validator, &$values, $id)
    {
        $values[ 'options' ] = json_encode([
            'name' => $validator->getInput('name')
        ]);
    }
}
