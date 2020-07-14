<?php

namespace SoosyzeCore\Block\Controller;

use Soosyze\Components\Validator\Validator;

class Section extends \Soosyze\Controller
{
    protected $pathViews;

    public function __construct()
    {
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function admin($theme, $req)
    {
        $styles  = self::template()->getBlock('this')->getVar('styles');
        $scripts = self::template()->getBlock('this')->getVar('scripts');

        $styles  .= '<link rel="stylesheet" href="' . self::core()->getPath('modules', 'modules/core', false) . '/Block/Assets/styles.css">';
        $scripts .= '<script src="' . self::core()->getPath('modules', 'modules/core', false) . '/Block/Assets/scripts.js"></script>';

        return self::template()
                ->getTheme($theme)
                ->view('page', [
                    'icon'       => '<i class="fa fa-columns" aria-hidden="true"></i>',
                    'title_main' => t('Editing blocks')
                ])
                ->view('this', [
                    'styles'  => $styles,
                    'scripts' => $scripts
                ])
                ->make('page.content', 'page-block-admin.php', $this->pathViews, [
                    'content'          => t('View and edit your site\'s display on the following topics.'),
                    'link_theme'       => self::router()->getRoute('block.section.admin', [
                        ':theme' => 'theme'
                    ]),
                    'link_theme_admin' => self::router()->getRoute('block.section.admin', [
                        ':theme' => 'theme_admin'
                    ])
        ]);
    }

    public function update($id, $req)
    {
        if (!self::query()->from('block')->where('block_id', '==', $id)->fetch()) {
            return $this->get404($req);
        }

        $validator = (new Validator())
            ->setRules([
                'weight'  => 'required|string|max:50',
                'section' => 'required|string|max:50'
            ])
            ->setInputs($req->getParsedBody());

        if ($validator->isValid()) {
            self::query()
                ->update('block', [
                    'weight'  => (int) $validator->getInput('weight'),
                    'section' => $validator->getInput('section')
                ])
                ->where('block_id', '==', $id)
                ->execute();
        }
    }
}
