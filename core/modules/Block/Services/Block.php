<?php

namespace SoosyzeCore\Block\Services;

class Block
{
    /**
     * @var \Soosyze\Config
     */
    private $config;

    /**
     * @var \Soosyze\App
     */
    private $core;

    /**
     * @var string
     */
    private $pathViews;

    public function __construct($config, $core)
    {
        $this->config    = $config;
        $this->core      = $core;
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function getBlocks()
    {
        $blocks = [
            'button'  => [
                'path'  => $this->pathViews,
                'title' => 'Text with button',
                'tpl'   => 'components/block/block-button.php'
            ],
            'card_ui' => [
                'path'  => $this->pathViews,
                'title' => 'Simple UI card',
                'tpl'   => 'components/block/block-card_ui.php'
            ],
            'code'    => [
                'path'  => $this->pathViews,
                'title' => 'Code',
                'tpl'   => 'components/block/block-code.php'
            ],
            'contact' => [
                'path'  => $this->pathViews,
                'title' => 'Contact',
                'tpl'   => 'components/block/block-contact.php'
            ],
            'gallery' => [
                'path'  => $this->pathViews,
                'title' => 'Picture Gallery',
                'tpl'   => 'components/block/block-gallery.php'
            ],
            'img'     => [
                'path'  => $this->pathViews,
                'title' => 'Image and text',
                'tpl'   => 'components/block/block-img.php'
            ],
            'map'     => [
                'path'  => $this->pathViews,
                'title' => 'Map',
                'tpl'   => 'components/block/block-map.php'
            ],
            'video'   => [
                'path'  => $this->pathViews,
                'title' => 'Video',
                'tpl'   => 'components/block/block-peertube.php'
            ],
            'social'  => [
                'path'  => $this->pathViews,
                'title' => 'Social networks',
                'tpl'   => 'components/block/block-social.php',
                'hook'  => 'social'
            ],
            'table'   => [
                'path'  => $this->pathViews,
                'title' => 'Table',
                'tpl'   => 'components/block/block-table.php'
            ],
            'text'    => [
                'path'  => $this->pathViews,
                'title' => 'Simple text',
                'tpl'   => 'components/block/block-text.php'
            ],
            'three'   => [
                'path'  => $this->pathViews,
                'title' => '3 columns',
                'tpl'   => 'components/block/block-three.php'
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
