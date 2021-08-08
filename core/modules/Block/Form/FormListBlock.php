<?php

declare(strict_types=1);

namespace SoosyzeCore\Block\Form;

class FormListBlock extends \Soosyze\Components\Form\FormBuilder
{
    /** @var array */
    protected $values = [
        'blocks'  => [],
        'section' => ''
    ];

    /** @var string */
    private $section;

    public function setSection(string $section): self
    {
        $this->section = $section;

        return $this;
    }

    public function makeFields(): self
    {
        $this->group('block-cards', 'div', function ($form) {
            foreach ($this->values[ 'blocks' ] as $key => $block) {
                $form->group("block-$key-group", 'div', function ($form) use ($block, $key) {
                    $form->radio('key_block', [
                            'id'    => "key_block-$key",
                            'value' => $key
                        ])
                        ->label('key_block-label', $this->getKeyBlockLabel($block), [
                            'data-link' => $block[ 'link_show_create' ],
                            'for'       => "key_block-$key"
                        ]);
                }, [ 'class' => 'block-card search_item' ]);
            }
        }, [ 'class' => 'block-cards form-group' ])
            ->group('submit-group', 'div', function ($form) {
                $form
                ->hidden('section', [ 'value' => $this->values[ 'section' ] ])
                ->submit('submit', t('Add'), [ 'class' => 'btn btn-success block-create-list' ]);
            }, [ 'class' => 'block_list-submit']);

        return $this;
    }

    private function getKeyBlockLabel(array $block): string
    {
        return '<span class="block-label">
                <span class="block-label-icon"><i class="' . $block[ 'icon' ] . '"></i></span>
                <span>
                    <span class="block-label-title search_text">' . t($block[ 'title' ]) . '</span>
                    <span class="block-label-descrition">' . t($block[ 'description' ]) . '</span>
                </span>
                </span>';
    }
}
