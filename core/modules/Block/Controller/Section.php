<?php

namespace SoosyzeCore\Block\Controller;

use Soosyze\Components\Validator\Validator;

class Section extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function admin($theme, $req)
    {
        $vendor = self::core()->getPath('modules', 'modules/core', false) . '/Block/Assets';

        return self::template()
                ->getTheme($theme === 'admin'
                    ? 'theme_admin'
                    : 'theme')
                ->addStyle('block', "$vendor/css/block.css")
                ->addScript('block', "$vendor/js/block.js")
                ->view('page', [
                    'icon'       => '<i class="fa fa-columns" aria-hidden="true"></i>',
                    'title_main' => t('Editing blocks')
                ])
                ->make('page.content', 'block/content-section-admin.php', $this->pathViews, [
                    'content'          => 'View and edit your site\'s display on the following topics.',
                    'link_theme'       => self::router()->getRoute('block.section.admin', [
                        ':theme' => 'public'
                    ]),
                    'link_theme_admin' => self::router()->getRoute('block.section.admin', [
                        ':theme' => 'admin'
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
