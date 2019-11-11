<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace SoosyzeCore\Block\Services;

/**
 * Description of Block
 *
 * @author mnoel
 */
class Block
{
    protected $core;

    public function __construct($core)
    {
        $this->core      = $core;
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function getBlocks()
    {
        $blocks = [
            'button'  => [
                'title' => t('Text with button'),
                'tpl'   => 'block-button.php',
                'path'  => $this->pathViews . 'blocks/'
            ],
            'card_ui' => [
                'title' => t('Simple UI card'),
                'tpl'   => 'block-card_ui.php',
                'path'  => $this->pathViews . 'blocks/'
            ],
            'code'    => [
                'title' => t('Code'),
                'tpl'   => 'block-code.php',
                'path'  => $this->pathViews . 'blocks/'
            ],
            'contact' => [
                'title' => t('Contact'),
                'tpl'   => 'block-contact.php',
                'path'  => $this->pathViews . 'blocks/'
            ],
            'gallery' => [
                'title' => t('Picture Gallery'),
                'tpl'   => 'block-gallery.php',
                'path'  => $this->pathViews . 'blocks/'
            ],
            'img'     => [
                'title' => t('Image and text'),
                'tpl'   => 'block-img.php',
                'path'  => $this->pathViews . 'blocks/'
            ],
            'map'     => [
                'title' => t('Map'),
                'tpl'   => 'block-map.php',
                'path'  => $this->pathViews . 'blocks/'
            ],
            'video'   => [
                'title' => t('Video'),
                'tpl'   => 'block-peertube.php',
                'path'  => $this->pathViews . 'blocks/'
            ],
            'social'  => [
                'title' => t('Social networks'),
                'tpl'   => 'block-social.php',
                'path'  => $this->pathViews . 'blocks/'
            ],
            'table'   => [
                'title' => t('Table'),
                'tpl'   => 'block-table.php',
                'path'  => $this->pathViews . 'blocks/'
            ],
            'text'    => [
                'title' => t('Simple text'),
                'tpl'   => 'block-text.php',
                'path'  => $this->pathViews . 'blocks/'
            ],
            'three'   => [
                'title' => t('3 columns'),
                'tpl'   => 'block-three.php',
                'path'  => $this->pathViews . 'blocks/'
            ]
        ];

        $this->core->callHook('block.create.form.data', [ &$blocks ]);

        return $blocks;
    }
}
