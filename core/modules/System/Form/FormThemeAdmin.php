<?php

namespace SoosyzeCore\System\Form;

class FormThemeAdmin extends \Soosyze\Components\Form\FormBuilder
{
    protected $values = [
        'theme_admin_dark' => ''
    ];

    protected static $attrGrp = [ 'class' => 'form-group' ];

    public function setValues(array $values)
    {
        $this->values = array_replace($this->values, $values);

        return $this;
    }

    public function makeFields()
    {
        return $this->group('fieldset-theme', 'fieldset', function ($form) {
            $form->legend('legend-theme', t('Settings'))
                    ->group('theme_admin_dark-group', 'div', function ($form) {
                        $form->checkbox('theme_admin_dark', [
                            'checked' => $this->values[ 'theme_admin_dark' ]
                        ])
                        ->label('theme_admin_dark-label', '<i class="ui" aria-hidden="true"></i> '
                            . t('Activate the dark mode for the administrator theme if available'), [
                            'for' => 'theme_admin_dark'
                        ]);
                    }, self::$attrGrp);
        });
    }
}
