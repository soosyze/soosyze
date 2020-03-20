<?php

namespace SoosyzeCore\Block\Services;

class Block
{
    protected $core;

    public function __construct($core)
    {
        $this->core      = $core;
        $this->pathViews = dirname(__DIR__) . '/Views/blocks/';
    }

    public function getBlocks()
    {
        $blocks = [
            'button'  => [
                'title' => t('Text with button'),
                'tpl'   => 'block-button.php',
                'path'  => $this->pathViews
            ],
            'card_ui' => [
                'title' => t('Simple UI card'),
                'tpl'   => 'block-card_ui.php',
                'path'  => $this->pathViews
            ],
            'code'    => [
                'title' => t('Code'),
                'tpl'   => 'block-code.php',
                'path'  => $this->pathViews
            ],
            'contact' => [
                'title' => t('Contact'),
                'tpl'   => 'block-contact.php',
                'path'  => $this->pathViews
            ],
            'gallery' => [
                'title' => t('Picture Gallery'),
                'tpl'   => 'block-gallery.php',
                'path'  => $this->pathViews
            ],
            'img'     => [
                'title' => t('Image and text'),
                'tpl'   => 'block-img.php',
                'path'  => $this->pathViews
            ],
            'map'     => [
                'title' => t('Map'),
                'tpl'   => 'block-map.php',
                'path'  => $this->pathViews
            ],
            'video'   => [
                'title' => t('Video'),
                'tpl'   => 'block-peertube.php',
                'path'  => $this->pathViews
            ],
            'social'  => [
                'title' => t('Social networks'),
                'tpl'   => 'block-social.php',
                'path'  => $this->pathViews
            ],
            'table'   => [
                'title' => t('Table'),
                'tpl'   => 'block-table.php',
                'path'  => $this->pathViews
            ],
            'text'    => [
                'title' => t('Simple text'),
                'tpl'   => 'block-text.php',
                'path'  => $this->pathViews
            ],
            'three'   => [
                'title' => t('3 columns'),
                'tpl'   => 'block-three.php',
                'path'  => $this->pathViews
            ]
        ];

        $this->core->callHook('block.create.form.data', [ &$blocks ]);

        return $blocks;
    }
}
