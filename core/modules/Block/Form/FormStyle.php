<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Block\Form;

use Soosyze\Core\Modules\Block\Enum\Background;
use Soosyze\Core\Modules\Block\Enum\Border;
use Soosyze\Core\Modules\Block\Enum\Font;
use Soosyze\Core\Modules\Block\Enum\Style;
use Soosyze\Core\Modules\FileSystem\Services\File;

class FormStyle extends \Soosyze\Components\Form\FormBuilder
{
    /**
     * @var array
     */
    protected $values = [
        'background_color'    => '',
        'background_image'    => '',
        'background_position' => '',
        'background_repeat'   => '',
        'background_size'     => '',
        'block_id'            => null,
        'border_color'        => '',
        'border_radius'       => 0,
        'border_style'        => '',
        'border_width'        => 0,
        'color_link'          => '',
        'color_text'          => '',
        'color_title'         => '',
        'font_family_text'    => '',
        'font_family_title'   => '',
        'margin'              => '',
        'margin_top'          => '',
        'margin_bottom'       => '',
        'margin_left'         => '',
        'margin_right'        => '',
        'padding'             => '',
        'padding_top'         => '',
        'padding_bottom'      => '',
        'padding_left'        => '',
        'padding_right'       => '',
    ];

    /**
     * @var array
     */
    private static $attrGrp = ['class' => 'form-group'];

    /**
     * @var File
     */
    private $file;

    public function __construct(array $attr, File $file)
    {
        parent::__construct($attr + ['class' => 'form-api']);
        $this->file = $file;
    }

    public function makeFields(): self
    {
        $this->colorsFields();
        $this->fontsFields();
        $this->backgroundFields();
        $this->bordersFields();
        $this->spacingFields();
        $this->group('submit-group', 'div', function ($form) {
            $form
                ->token('token_style')
                ->submit('submit', t('Save'), ['class' => 'btn btn-success']);
        });

        return $this;
    }

    public function backgroundFields(): void
    {
        $this->group('background_image-fieldset', 'fieldset', function ($form) {
            $form
                ->legend('background_image-legend', t('Background'))
                ->group('background_color-group', 'div', function ($form) {
                    $form->label('background_color-label', t('Background color'))
                        ->text('background_color', [
                            'class' => 'form-control color-picker',
                            'value' => $this->values['background_color']
                        ]);
                }, self::$attrGrp)
                ->group('background_image-group', 'div', function ($form) {
                    $form->label('background_image-label', t('Image'), ['for' => 'background_image']);
                    $this->file->inputFile('background_image', $form, $this->values['background_image']);
                }, self::$attrGrp)
                ->group('background_repeat-group', 'div', function ($form) {
                    $form->label('background_repeat-label', t('Repeat'))
                        ->select('background_repeat', self::getOptionsRepeat(), [
                            ':selected' => $this->values['background_repeat'],
                            'class'     => 'form-control'
                        ]);
                }, self::$attrGrp)
                ->group('background_position-group', 'div', function ($form) {
                    $form->label('background_position-label', t('Position'))
                        ->select('background_position', self::getOptionsPosition(), [
                            ':selected' => $this->values['background_position'],
                            'class'     => 'form-control'
                        ]);
                }, self::$attrGrp)
                ->group('background_size-group', 'div', function ($form) {
                    $form->label('background_size-label', t('Size'))
                        ->select('background_size', self::getOptionsBackgroundSize(), [
                            ':selected' => $this->values['background_size'],
                            'class'     => 'form-control'
                        ]);
                }, self::$attrGrp);
        }, [
            'class' => 'tab-pane fade',
            'id'    => 'background_image-fieldset'
        ]);
    }

    public function spacingFields(): void
    {
        $this
            ->group('spacing-fieldset', 'fieldset', function ($form) {
                $form
                    ->legend('spacing-legend', t('Spacing'))
                    ->group('margin-group', 'div', function ($form) {
                        $form->label('margin-label', t('Marging'), [
                            'data-tooltip' => t('Size expressed pixels')
                        ])
                            ->number('margin', [
                                'class'    => 'form-control',
                                'value'    => $this->values['margin'],
                            ]);
                    }, self::$attrGrp)
                    ->group('margin_top-group', 'div', function ($form) {
                        $form->label('margin_top-label', t('Top'), [
                            'data-tooltip' => t('Size expressed pixels')
                        ])
                            ->number('margin_top', [
                                'class'    => 'form-control',
                                'value'    => $this->values['margin_top'],
                            ]);
                    }, self::$attrGrp)
                    ->group('margin_bottom-group', 'div', function ($form) {
                        $form->label('margin_bottom-label', t('Bottom'), [
                            'data-tooltip' => t('Size expressed pixels')
                        ])
                            ->number('margin_bottom', [
                                'class'    => 'form-control',
                                'value'    => $this->values['margin_bottom'],
                            ]);
                    }, self::$attrGrp)
                    ->group('margin_left-group', 'div', function ($form) {
                        $form->label('margin_left-label', t('Left'), [
                            'data-tooltip' => t('Size expressed pixels')
                        ])
                            ->number('margin_left', [
                                'class'    => 'form-control',
                                'value'    => $this->values['margin_left'],
                            ]);
                    }, self::$attrGrp)
                    ->group('margin_right-group', 'div', function ($form) {
                        $form->label('margin_right-label', t('Right'), [
                            'data-tooltip' => t('Size expressed pixels')
                        ])
                            ->number('margin', [
                                'class'    => 'form-control',
                                'value'    => $this->values['margin_right'],
                            ]);
                    }, self::$attrGrp)
                    ->group('padding-group', 'div', function ($form) {
                        $form->label('padding-label', t('Padding'), [
                            'data-tooltip' => t('Size expressed pixels')
                        ])
                            ->number('padding', [
                                'class'    => 'form-control',
                                'value'    => $this->values['padding'],
                            ]);
                    }, self::$attrGrp)
                    ->group('padding_top-group', 'div', function ($form) {
                        $form->label('padding_top-label', t('Top'), [
                            'data-tooltip' => t('Size expressed pixels')
                        ])
                            ->number('padding_top', [
                                'class'    => 'form-control',
                                'value'    => $this->values['padding_top'],
                            ]);
                    }, self::$attrGrp)
                    ->group('padding_bottom-group', 'div', function ($form) {
                        $form->label('padding_bottom-label', t('Bottom'), [
                            'data-tooltip' => t('Size expressed pixels')
                        ])
                            ->number('padding_bottom', [
                                'class'    => 'form-control',
                                'value'    => $this->values['padding_bottom'],
                            ]);
                    }, self::$attrGrp)
                    ->group('padding_left-group', 'div', function ($form) {
                        $form->label('padding_left-label', t('Left'), [
                            'data-tooltip' => t('Size expressed pixels')
                        ])
                            ->number('padding_left', [
                                'class'    => 'form-control',
                                'value'    => $this->values['padding_left'],
                            ]);
                    }, self::$attrGrp)
                    ->group('padding_right-group', 'div', function ($form) {
                        $form->label('padding_right-label', t('Right'), [
                            'data-tooltip' => t('Size expressed pixels')
                        ])
                            ->number('padding_right', [
                                'class'    => 'form-control',
                                'value'    => $this->values['padding_right'],
                            ]);
                    }, self::$attrGrp);
            }, [
                'class' => 'tab-pane fade',
                'id'    => 'spacing-fieldset'
            ]);
    }

    private function colorsFields(): void
    {
        $this->group('color-fieldset', 'fieldset', function ($form) {
            $form
                ->legend('color-legend', t('Colors'))
                ->group('color_title-group', 'div', function ($form) {
                    $form->label('color_title-label', t('Title color'))
                        ->text('color_title', [
                            'class' => 'form-control color-picker',
                            'value' => $this->values['color_title']
                        ]);
                }, self::$attrGrp)
                ->group('color_text-group', 'div', function ($form) {
                    $form->label('color_text-label', t('Text color'))
                        ->text('color_text', [
                            'class' => 'form-control color-picker',
                            'value' => $this->values['color_text']
                        ]);
                }, self::$attrGrp)
                ->group('color_link-group', 'div', function ($form) {
                    $form->label('color_link-label', t('Link color'))
                        ->text('color_link', [
                            'class' => 'form-control color-picker',
                            'value' => $this->values['color_link']
                        ]);
                }, self::$attrGrp);
        }, [
            'class' => 'tab-pane fade active',
            'id'    => 'color-fieldset'
        ]);
    }

    private function fontsFields(): void
    {
        $this->group('font-fieldset', 'fieldset', function ($form) {
            $form
                ->legend('font-legend', t('Font'))
                ->group('font_family_text-group', 'div', function ($form) {
                    $form->label('font_family_text-label', t('Text font'))
                        ->select('font_family_text', $this->getOptionsFont(), [
                            ':selected' => $this->values['font_family_text'],
                            'class'     => 'form-control'
                        ]);
                }, self::$attrGrp)
                ->group('font_family_title-group', 'div', function ($form) {
                    $form->label('font_family_title-label', t('Title font'))
                        ->select('font_family_title', $this->getOptionsFont(), [
                            ':selected' => $this->values['font_family_title'],
                            'class'     => 'form-control'
                        ]);
                }, self::$attrGrp);
        }, [
            'class' => 'tab-pane fade',
            'id'    => 'font-fieldset'
        ]);
    }

    private function bordersFields(): void
    {
        $this->group('border-fieldset', 'fieldset', function ($form) {
            $form
                ->legend('border-legend', t('Border'))
                ->group('border_style-group', 'div', function ($form) {
                    $form->label('border_style-label', t('Border style'))
                        ->select('border_style', $this->getOptionsStyleBorder(), [
                            ':selected' => $this->values['border_style'],
                            'class'     => 'form-control'
                        ]);
                }, self::$attrGrp)
                ->group('border_width-group', 'div', function ($form) {
                    $form->label('border_width-label', t('Border width'), [
                        'data-tooltip' => t('Size expressed pixels')
                    ])
                        ->number('border_width', [
                            'class'    => 'form-control',
                            'min'      => 0,
                            'value'    => $this->values['border_width'],
                        ]);
                }, self::$attrGrp)
                ->group('border_color-group', 'div', function ($form) {
                    $form->label('border_color-label', t('Border color'))
                        ->text('image', [
                            'class' => 'form-control color-picker',
                            'value' => $this->values['border_color']
                        ]);
                }, self::$attrGrp)
                ->group('border_radius-group', 'div', function ($form) {
                    $form->label('border_radius-label', t('Rounding of angles'), [
                        'data-tooltip' => t('Size expressed pixels')
                    ])
                        ->number('border_radius', [
                            'class'    => 'form-control',
                            'min'      => 0,
                            'value'    => $this->values['border_radius'],
                        ]);
                }, self::$attrGrp);
        }, [
            'class' => 'tab-pane fade',
            'id'    => 'border-fieldset'
        ]);
    }

    private static function getOptionsRepeat(): array
    {
        $options[] = ['label' => t('-- Select --'), 'value' => ''];
        foreach (Background::REPEAT as $value => $label) {
            $options[] = ['label' => t($label), 'value' => $value];
        }

        return $options;
    }

    private static function getOptionsStyleBorder(): array
    {
        $options[] = ['label' => t('-- Select --'), 'value' => ''];
        foreach (Border::STYLE as $style) {
            $options[] = ['label' => $style, 'value' => $style];
        }

        return $options;
    }

    private static function getOptionsPosition(): array
    {
        $options[0] = ['label' => t('-- Select --'), 'value' => ''];
        foreach (Background::POSITIONS as $key => $position) {
            $options[$key + 1]['label'] = t($position['label']);
            foreach ($position['value'] as $value => $label) {
                $options[$key + 1]['value'][] = ['label' => t($label), 'value' => $value];
            }
        }

        return $options;
    }

    private static function getOptionsBackgroundSize(): array
    {
        $options[0] = ['label' => t('-- Select --'), 'value' => ''];
        foreach (Background::SIZE as $size) {
            $options[] = ['label' => $size, 'value' => $size];
        }

        return $options;
    }

    private static function getOptionsFont(): array
    {
        $options[0] = ['label' => t('-- Select --'), 'value' => ''];
        foreach (Font::FONTS as $key => $font) {
            $options[$key + 1]['label'] = $font['label'];
            foreach ($font['value'] as $value) {
                $options[$key + 1]['value'][] = [
                    'label' => $value,
                    'value' => $value,
                    'attr'  => ['style' => "font-family:'$value'"]
                ];
            }
        }

        return $options;
    }
}
