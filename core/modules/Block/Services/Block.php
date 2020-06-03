<?php

namespace SoosyzeCore\Block\Services;

class Block
{
    protected $config;
    
    protected $core;
    
    protected $pathViews;

    public function __construct($core, $config)
    {
        $this->core      = $core;
        $this->config    = $config;
        $this->pathViews = dirname(__DIR__) . '/Views/blocks/';
    }

    public function getBlocks()
    {
        $blocks = [
            'button'  => [
                'path'  => $this->pathViews,
                'title' => t('Text with button'),
                'tpl'   => 'block-button.php'
            ],
            'card_ui' => [
                'path'  => $this->pathViews,
                'title' => t('Simple UI card'),
                'tpl'   => 'block-card_ui.php'
            ],
            'code'    => [
                'path'  => $this->pathViews,
                'title' => t('Code'),
                'tpl'   => 'block-code.php'
            ],
            'contact' => [
                'path'  => $this->pathViews,
                'title' => t('Contact'),
                'tpl'   => 'block-contact.php'
            ],
            'gallery' => [
                'path'  => $this->pathViews,
                'title' => t('Picture Gallery'),
                'tpl'   => 'block-gallery.php'
            ],
            'img'     => [
                'path'  => $this->pathViews,
                'title' => t('Image and text'),
                'tpl'   => 'block-img.php'
            ],
            'map'     => [
                'path'  => $this->pathViews,
                'title' => t('Map'),
                'tpl'   => 'block-map.php'
            ],
            'video'   => [
                'path'  => $this->pathViews,
                'title' => t('Video'),
                'tpl'   => 'block-peertube.php'
            ],
            'social'  => [
                'path'  => $this->pathViews,
                'title' => t('Social networks'),
                'tpl'   => 'block-social.php',
                'hook'  => 'social'
            ],
            'table'   => [
                'path'  => $this->pathViews,
                'title' => t('Table'),
                'tpl'   => 'block-table.php'
            ],
            'text'    => [
                'path'  => $this->pathViews,
                'title' => t('Simple text'),
                'tpl'   => 'block-text.php'
            ],
            'three'   => [
                'path'  => $this->pathViews,
                'title' => t('3 columns'),
                'tpl'   => 'block-three.php'
            ]
        ];

        $this->core->callHook('block.create.form.data', [ &$blocks ]);

        return $blocks;
    }
    
    public function hookBlockSocial($tpl, array $options)
    {
        return $tpl->addVar(
            'icon_socials',
            $this->config->get('settings.icon_socials')
        );
    }
}
