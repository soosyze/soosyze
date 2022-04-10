<?php

declare(strict_types=1);

namespace SoosyzeCore\System\Form;

class FormThemeAdmin extends \Soosyze\Components\Form\FormBuilder
{
    public const THEME_ADMIN_DARK = true;

    /**
     * @var array
     */
    protected $values = [
        'theme_admin_dark' => self::THEME_ADMIN_DARK
    ];

    /**
     * @var array
     */
    private static $attrGrp = [ 'class' => 'form-group' ];

    public function makeFields(): self
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
        })
                ->group('submit-group', 'div', function ($form) {
                    $form->token('setting_theme')
                    ->submit('submit', t('Save'), [ 'class' => 'btn btn-success' ])
                    ->html('cancel', '<button:attr>:content</button>', [
                        ':content' => t('Cancel'),
                        'class'    => 'btn btn-danger',
                        'onclick'  => 'javascript:history.back();',
                        'type'     => 'button'
                    ]);
                });
    }
}
