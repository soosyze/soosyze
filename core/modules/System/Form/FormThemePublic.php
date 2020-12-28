<?php

namespace SoosyzeCore\System\Form;

class FormThemePublic extends \Soosyze\Components\Form\FormBuilder
{
    /**
     * @var
     */
    protected $file;

    protected $values = [
        'favicon' => '',
        'logo'    => ''
    ];

    protected static $attrGrp = [ 'class' => 'form-group' ];

    public function __construct(array $attr, $file)
    {
        parent::__construct($attr);
        $this->file = $file;
    }

    public function setValues(array $values)
    {
        $this->values = array_replace($this->values, $values);

        return $this;
    }

    public function makeFields()
    {
        return $this->group('fieldset-theme', 'fieldset', function ($form) {
            $form->legend('legend-theme', t('Settings'))
                    ->group('logo-group', 'div', function ($form) {
                        $form->label('logo-label', t('Logo'), [
                            'class'        => 'control-label',
                            'data-tooltip' => '200ko maximum.',
                            'for'          => 'logo'
                        ]);
                        $this->file->inputFile('logo', $form, $this->values[ 'logo' ]);
                    }, self::$attrGrp)
                    ->group('group-favicon', 'div', function ($form) {
                        $form->label('favicon-label', t('Favicon'), [
                            'class'        => 'control-label',
                            'data-tooltip' => t('Image to the left of the title of your browser window.'),
                            'for'          => 'favicon'
                        ]);
                        $this->file->inputFile('favicon', $form, $this->values[ 'favicon' ]);
                        $form->html('favicon-info-size', '<p:attr>:content</p>', [
                            ':content' => t('The file must weigh less than 100 KB.')
                        ])->html('favicon-info-dimensions', '<p:attr>:content</p>', [
                            ':content' => t('The width and height min and max: 16px and 310px.')
                        ]);
                    }, self::$attrGrp);
        });
    }
}
