<?php

declare(strict_types=1);

namespace Soosyze\Core\Modules\Block\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Util\Util;
use Soosyze\Components\Validator\Validator;
use Soosyze\Core\Modules\Block\Enum\Background;
use Soosyze\Core\Modules\Block\Enum\Border;
use Soosyze\Core\Modules\Block\Enum\Font;
use Soosyze\Core\Modules\Block\Form\FormStyle;
use Soosyze\Core\Modules\Block\Model;
use Soosyze\Core\Modules\Template\Services\Block as ServiceBlock;

/**
 * @method \Soosyze\Core\Modules\Block\Services\Block         block()
 * @method \Soosyze\Core\Modules\FileSystem\Services\File     file()
 * @method \Soosyze\Core\Modules\QueryBuilder\Services\Query  query()
 * @method \Soosyze\Core\Modules\Block\Services\Style         style()
 * @method \Soosyze\Core\Modules\Template\Services\Templating template()
 */
class Style extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    /**
     * @return ServiceBlock|ResponseInterface
     */
    public function edit(
        string $theme,
        int $id,
        ServerRequestInterface $req
    ) {
        if (!$this->find($id)) {
            return $this->get404($req);
        }

        $themeName = $theme === 'admin'
            ? self::template()->getThemeAdminName()
            : self::template()->getThemePublicName();

        /** @var array $values */
        $values = self::style()->getSettingsBlocksStyle($themeName)[$id] ?? [];

        $this->container->callHook('block.style.edit.form.data', [
            &$values, $theme, $id
        ]);

        $action = self::router()->generateUrl('block.style.update', [
            'theme' => $theme,
            'id'    => $id
        ]);

        $form = (new FormStyle([
            'action'  => $action,
            'class'   => 'form-api',
            'enctype' => 'multipart/form-data',
            'method'  => 'put'
        ], self::file())
        )
            ->setValues($values)
            ->makeFields();

        $this->container->callHook('block.style.edit.form', [
            &$form, $values, $theme, $id
        ]);

        return self::template()
            ->getTheme('theme_admin')
            ->createBlock('block/modal-form.php', $this->pathViews)
            ->addVars([
                'fieldset_submenu' => $this->getStyleFieldsetSubmenu(),
                'form'             => $form,
                'menu'             => self::block()->getBlockSubmenu('block.style.edit', $theme, $id),
                'title'            => t('Block style')
            ]);
    }

    public function update(string $theme, int $id, ServerRequestInterface $req): ResponseInterface
    {
        if (!($block = $this->find($id))) {
            return $this->json(404, [
                'messages' => ['errors' => [t('The requested resource does not exist.')]]
            ]);
        }

        $validator = $this->getValidator($req, $theme, $id);

        $this->container->callHook('block.style.update.validator', [
            &$validator, $theme, $id
        ]);

        if ($validator->isValid()) {
            $data = $this->getData($validator);

            $this->container->callHook('block.style.update.before', [
                $validator, &$data, $theme, $id
            ]);

            $themeName = $theme === 'admin'
                ? self::template()->getThemeAdminName()
                : self::template()->getThemePublicName();

            $config = self::style()->getSettingsBlocksStyle($themeName);

            $config[$id] = $data;
            self::style()->setSettingsBlocksStyle($themeName, $config);

            self::style()->saveBackgroundImage($themeName, $id, $validator);

            $this->container->callHook('block.style.update.after', [
                $validator, $data, $theme, $id
            ]);

            $this->generateStyleCssFile([$themeName]);

            return $this->json(200, [
                'redirect' => self::router()->generateUrl('block.section.admin', [
                    'theme' => $theme
                ])
            ]);
        }

        return $this->json(400, [
            'messages'    => ['errors' => $validator->getKeyErrors()],
            'errors_keys' => $validator->getKeyInputErrors()
        ]);
    }

    public function styleGenerate(): ResponseInterface
    {
        $themeAdminName  = self::template()->getThemeAdminName();
        $themePublicName = self::template()->getThemePublicName();

        $this->generateStyleCssFile([$themePublicName]);

        $_SESSION['messages']['success'][] = t('The style of the theme have been updated');

        return new Redirect(self::router()->generateUrl('system.tool.admin'), 302);
    }

    private function getStyleFieldsetSubmenu(): ServiceBlock
    {
        $menu = [
            [
                'class'      => 'active',
                'icon'       => 'fas fa-palette',
                'link'       => '#color-fieldset',
                'title_link' => t('Colors')
            ], [
                'class'      => '',
                'icon'       => 'fas fa-image',
                'link'       => '#background_image-fieldset',
                'title_link' => t('Background')
            ], [
                'class'      => '',
                'icon'       => 'fas fa-paragraph',
                'link'       => '#font-fieldset',
                'title_link' => t('Font')
            ], [
                'class'      => '',
                'icon'       => 'fas fa-border-style',
                'link'       => '#border-fieldset',
                'title_link' => t('Border')
            ], [
                'class'      => '',
                'icon'       => 'fas fa-expand-arrows-alt',
                'link'       => '#margin-fieldset',
                'title_link' => t('Spacing')
            ]
        ];

        $this->container->callHook('block.style.fieldset.submenu', [&$menu]);

        return self::template()
            ->getTheme('theme_admin')
            ->createBlock('block/submenu-block_fieldset.php', $this->pathViews)
            ->addVars([
                'menu' => $menu
            ]);
    }

    private function find(int $id): ?array
    {
        return self::query()
            ->from('block')
            ->where('block_id', '=', $id)
            ->fetch();
    }

    private function getValidator(
        ServerRequestInterface $req,
        string $theme,
        ?int $id = null
    ): Validator {
        $rules = [
            'background_image'    => '!required|image:jpeg,jpg,png|max:1Mb',
            'background_color'    => '!required|string',
            'background_position' => '!required|inarray:' . Background::getPositionsImplode(),
            'background_repeat'   => '!required|inarray:' . Background::getRepeatImplode(),
            'background_size'     => '!required|inarray:' . Background::getSizesImplode(),
            'border_color'        => '!required|string',
            'border_radius'       => '!required|numeric|min_numeric:0',
            'border_style'        => '!required|inarray:' . Border::getStylesImplode(),
            'border_width'        => '!required|numeric|min_numeric:0',
            'color_link'          => '!required|string',
            'color_text'          => '!required|string',
            'color_title'         => '!required|string',
            'font_family_text'    => '!required|inarray:' . Font::getImplode(),
            'font_family_title'   => '!required|inarray:' . Font::getImplode(),
            'margin'              => '!required|numeric',
            'margin_top'          => '!required|numeric',
            'margin_bottom'       => '!required|numeric',
            'margin_left'         => '!required|numeric',
            'margin_right'        => '!required|numeric',
            'padding'             => '!required|numeric',
            'padding_top'         => '!required|numeric',
            'padding_bottom'      => '!required|numeric',
            'padding_left'        => '!required|numeric',
            'padding_right'       => '!required|numeric',
            'token_style'         => 'token'
        ];

        return (new Validator())
            ->setRules($rules)
            ->setLabels([
                'background_image'    => t('Image'),
                'background_color'    => t('Background color'),
                'background_position' => t('Position'),
                'background_repeat'   => t('Repeat'),
                'background_size'     => t('Size'),
                'border_color'        => t('Border color'),
                'border_radius'       => t('Rounding of angles'),
                'border_style'        => t('Border style'),
                'border_width'        => t('Border width'),
                'color_link'          => t('Link color'),
                'color_text'          => t('Text color'),
                'color_title'         => t('Title color'),
                'font_family_text'    => t('Text font'),
                'font_family_title'   => t('Title font'),
                'margin'              => t('Marging'),
                'margin_top'          => t('Marging top'),
                'margin_bottom'       => t('Marging bottom'),
                'margin_left'         => t('Marging left'),
                'margin_right'        => t('Marging right'),
                'padding'             => t('Padding'),
                'padding_top'         => t('Padding top'),
                'padding_bottom'      => t('Padding bottom'),
                'padding_left'        => t('Padding left'),
                'padding_right'       => t('Padding right')
            ])
            ->setInputs(
                $req->getParsedBody() + $req->getUploadedFiles()
            );
    }

    private function getData(Validator $validator): Model\Style
    {
        return Model\Style::createFromArray([
            'background_color'    => $validator->getInputString('background_color'),
            'background_position' => $validator->getInputString('background_position'),
            'background_repeat'   => $validator->getInputString('background_repeat'),
            'background_size'     => $validator->getInputString('background_size'),
            'border_color'        => $validator->getInputString('border_color'),
            'border_radius'       => $validator->getInputInt('border_radius'),
            'border_style'        => $validator->getInputString('border_style'),
            'border_width'        => $validator->getInputInt('border_width'),
            'color_link'          => $validator->getInputString('color_link'),
            'color_text'          => $validator->getInputString('color_text'),
            'color_title'         => $validator->getInputString('color_title'),
            'font_family_text'    => $validator->getInputString('font_family_text'),
            'font_family_title'   => $validator->getInputString('font_family_title'),
            'margin'              => $validator->getInputInt('margin'),
            'padding'             => $validator->getInputInt('padding'),
        ]);
    }

    private function generateStyleCssFile(array $themesName): void
    {
        foreach ($themesName as $themeName) {
            /** @var array $styles */
            $styles    = self::config()->get("template-$themeName", []) ?? [];
            $stylesCss = '';

            foreach ($styles['sections'] ?? [] as $selector => $data) {
                $stylesCss .= self::style()->generateStyleCssBlock(
                    '#section-' . $selector,
                    Model\Style::createFromArray((array) $data)
                );
            }
            foreach ($styles['block'] ?? [] as $selector => $data) {
                $stylesCss .= self::style()->generateStyleCssBlock(
                    '#block-' . $selector,
                    Model\Style::createFromArray((array) $data)
                );
            }

            $handle = Util::tryFopen(self::style()->getPathStyle($themeName), 'w+');
            fwrite($handle, $stylesCss);
            fclose($handle);
        }
    }
}
