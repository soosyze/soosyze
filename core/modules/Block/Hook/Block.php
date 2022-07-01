<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Block\Hook;

use Soosyze\Components\Form\FormGroupBuilder;
use Soosyze\Components\Router\Router;
use Soosyze\Components\Validator\Validator;
use Soosyze\Config;
use Soosyze\Core\Modules\Filter\Services\LazyLoding;
use Soosyze\Core\Modules\Filter\Services\Xss;
use Soosyze\Core\Modules\Template\Services\Block as ServiceBlock;

/**
 * @phpstan-type BlockHook array{
 *     description: string,
 *     hook?: string,
 *     icon: string,
 *     options?: array,
 *     path: string,
 *     title: string,
 *     tpl: string
 * }
 */
class Block implements \Soosyze\Core\Modules\Block\BlockInterface
{
    private const TAG_IFRAME = [
        'iframe' => [
            'allowfullscreen' => 1,
            'class'           => 1,
            'data-src'        => 1,
            'frameborder'     => 1,
            'height'          => 1,
            'loading'         => 1,
            'marginheight'    => 1,
            'marginwidth'     => 1,
            'name'            => 1,
            'referrerpolicy'  => 1,
            'sandbox'         => 1,
            'scrolling'       => 1,
            'src'             => 1,
            'srcdoc'          => 1,
            'title'           => 1,
            'width'           => 1
        ]
    ];

    /**
     * @var string
     */
    private const PATH_VIEWS = __DIR__ . '/../Views/';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var LazyLoding
     */
    private $lazyloading;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var Xss
     */
    private $xss;

    public function __construct(
        Config $config,
        LazyLoding $lazyloading,
        Router $router,
        Xss $xss
    ) {
        $this->config      = $config;
        $this->lazyloading = $lazyloading;
        $this->router      = $router;
        $this->xss         = $xss->getKses();
    }

    public function hookBlockCreateFormData(array &$blocks): void
    {
        $blocks += [
            'code'    => [
                'description' => t('Displays a block of code.'),
                'icon'        => 'fas fa-code',
                'path'        => self::PATH_VIEWS,
                'title'       => t('Code'),
                'tpl'         => 'components/block/block-code.php'
            ],
            'contact' => [
                'description' => t('Displays your contact details.'),
                'icon'        => 'fas fa-address-card',
                'path'        => self::PATH_VIEWS,
                'title'       => t('Contact'),
                'tpl'         => 'components/block/block-contact.php'
            ],
            'img'     => [
                'description' => t('Displays an image and text.'),
                'icon'        => 'fas fa-image',
                'path'        => self::PATH_VIEWS,
                'title'       => t('Image and text'),
                'tpl'         => 'components/block/block-img.php'
            ],
            'map'     => [
                'description' => t('Displays a map.'),
                'hook'        => 'map',
                'icon'        => 'fas fa-map',
                'options'     => [
                    'code_integration' => '<iframe width="100%" height="315" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://www.openstreetmap.org/export/embed.html?bbox=2.1193313598632817%2C48.74985082796366%2C2.5725173950195317%2C48.95069008682183&amp;layer=mapnik" style="border: 1px solid black" title="Carte openstreetmap"></iframe>'
                ],
                'path'        => self::PATH_VIEWS,
                'title'       => t('Map'),
                'tpl'         => 'components/block/block-map.php'
            ],
            'social'  => [
                'description' => t('List of your social networks.'),
                'hook'        => 'social',
                'icon'        => 'fas fa-share-alt',
                'path'        => self::PATH_VIEWS,
                'title'       => t('Social networks'),
                'tpl'         => 'components/block/block-social.php'
            ],
            'table'   => [
                'description' => t('Display a table.'),
                'icon'        => 'fas fa-table',
                'path'        => self::PATH_VIEWS,
                'title'       => t('Table'),
                'tpl'         => 'components/block/block-table.php'
            ],
            'text'    => [
                'description' => t('Displays arbitrary text.'),
                'icon'        => 'fas fa-paragraph',
                'path'        => self::PATH_VIEWS,
                'title'       => t('Simple text'),
                'tpl'         => 'components/block/block-text.php'
            ],
            'video'   => [
                'description' => t('Displays a video.'),
                'hook'        => 'video',
                'icon'        => 'fas fa-video',
                'options'     => [
                    'code_integration' => '<iframe width="100%" height="315" sandbox="allow-same-origin allow-scripts allow-popups" src="https://video.blender.org/videos/embed/3d95fb3d-c866-42c8-9db1-fe82f48ccb95" frameborder="0" allowfullscreen></iframe>'
                ],
                'path'        => self::PATH_VIEWS,
                'title'       => t('Video'),
                'tpl'         => 'components/block/block-video.php'
            ]
        ];
    }

    public function hookSocial(ServiceBlock $tpl, ?array $options): ServiceBlock
    {
        return $tpl->addVar(
            'icon_socials',
            $this->config->get('settings.icon_socials')
        );
    }

    public function hookMap(ServiceBlock $tpl, ?array $options): ServiceBlock
    {
        return $tpl->addVar(
            'code_integration',
            $this->filterIframe($options[ 'code_integration' ] ?? '')
        );
    }

    public function hookMapForm(FormGroupBuilder &$form, array $values): void
    {
        $form->group('map-fieldset', 'fieldset', function ($form) use ($values) {
            $form->legend('map-legend', t('Settings'))
                ->group('limit-group', 'div', function ($form) use ($values) {
                    $form->label('code_integration-label', t('Embed code'), [
                        'data-tooltip' => t('Embed code provided by map sites in sharing options.')
                    ])
                    ->text('code_integration', [
                        'class'    => 'form-control',
                        'required' => 1,
                        'value'    => $values[ 'options' ][ 'code_integration' ],
                    ]);
                }, [ 'class' => 'form-group' ]);
        });
    }

    public function hookSocialForm(FormGroupBuilder &$form, array $values): void
    {
        $form->group('social-fieldset', 'fieldset', function ($form) {
            $form->legend('social-legend', t('Settings'))
                ->group('social-group', 'div', function ($form) {
                    $form->html('code_integration-label', '<a:attr>:content</a>', [
                        ':content' => t('Configure the list of your social networks from the configuration interface of your site'),
                        'href'     => $this->router->generateUrl('config.edit', [
                            'id' => 'social'
                        ]),
                        'target'   => '_blank'
                    ]);
                }, [ 'class' => 'form-group' ]);
        });
    }

    public function hookMapValidator(Validator &$validator): void
    {
        $validator
            ->addRule('code_integration', 'required|string')
            ->addLabel('code_integration', t('Embed code'));
    }

    public function hookMapBefore(Validator $validator, array &$data): void
    {
        $data[ 'options' ] = json_encode([
            'code_integration' => $validator->getInput('code_integration')
        ]);
    }

    public function hookVideo(ServiceBlock $tpl, array $options): ServiceBlock
    {
        return $tpl->addVar(
            'code_integration',
            $this->filterIframe($options[ 'code_integration' ])
        );
    }

    public function hookVideoForm(FormGroupBuilder &$form, array $values): void
    {
        $form->group('video-fieldset', 'fieldset', function ($form) use ($values) {
            $form->legend('video-legend', t('Settings'))
                ->group('limit-group', 'div', function ($form) use ($values) {
                    $form->label('code_integration-label', t('Embed code'), [
                        'data-tooltip' => t('Embed code provided by video sites in sharing options.')
                    ])
                    ->text('code_integration', [
                        'class'    => 'form-control',
                        'required' => 1,
                        'value'    => $values[ 'options' ][ 'code_integration' ],
                    ]);
                }, [ 'class' => 'form-group' ]);
        });
    }

    public function hookVideoValidator(Validator &$validator): void
    {
        $validator
            ->addRule('code_integration', 'required|string')
            ->addLabel('code_integration', t('Embed code'));
    }

    public function hookVideoBefore(Validator $validator, array &$data): void
    {
        $data[ 'options' ] = json_encode([
            'code_integration' => $validator->getInput('code_integration')
        ]);
    }

    private function filterIframe(string $str): string
    {
        $this->xss->setAllowedTags(self::TAG_IFRAME);
        $str = $this->xss->filter($str);

        return $this->lazyloading->filter($str);
    }
}
