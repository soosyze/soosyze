<?php

declare(strict_types=1);

namespace SoosyzeCore\Block\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soosyze\Components\Validator\Validator;
use SoosyzeCore\Template\Services\Block;

/**
 * @method \SoosyzeCore\QueryBuilder\Services\Query  query()
 * @method \SoosyzeCore\Template\Services\Templating template()
 */
class Section extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathViews = dirname(__DIR__) . '/Views/';
    }

    public function admin(string $theme, ServerRequestInterface $req): ResponseInterface
    {
        $vendor = self::core()->getPath('modules', 'modules/core', false) . '/Block/Assets';

        return self::template()
                ->getTheme(
                    $theme === 'admin'
                    ? 'theme_admin'
                    : 'theme'
                )
                ->addStyle('block', "$vendor/css/block.css")
                ->addScript('block', "$vendor/js/block.js")
                ->view('page', [
                    'icon'       => '<i class="fa fa-columns" aria-hidden="true"></i>',
                    'title_main' => t('Editing blocks')
                ])
                ->make('page.content', 'block/content-section-admin.php', $this->pathViews, [
                    'content'          => $theme === 'admin'
                    ? 'Edit public theme blocks'
                    : 'Edit admin theme blocks',
                    'link_theme_index' => self::router()->generateUrl('system.theme.index'),
                    'link_section'     => self::router()->generateUrl('block.section.admin', [
                        'theme' => $theme === 'admin'
                        ? 'public'
                        : 'admin'
                    ])
        ]);
    }

    public function update(int $id, ServerRequestInterface $req): ResponseInterface
    {
        if (!self::query()->from('block')->where('block_id', '=', $id)->fetch()) {
            return $this->json(404, [
                    'messages' => [ 'errors' => [ t('The requested resource does not exist.') ] ]
            ]);
        }

        $validator = (new Validator())
            ->setRules([ 'block' => 'array' ])
            ->setInputs((array) $req->getParsedBody());

        if (!$validator->isValid()) {
            return $this->json(400, [
                'messages'    => [ 'errors' => $validator->getKeyErrors() ],
                'errors_keys' => $validator->getKeyInputErrors()
            ]);
        }

        $this->container->callHook('block.section.update.validator', [
            &$validator, $id
        ]);

        $blocks = $validator->getInputArray('block');

        $validatorBlock = self::getValidator($blocks);

        if ($validatorBlock->isValid()) {
            foreach ($blocks as $idBlock => $value) {
                $data = [
                    'weight'  => $value[ 'weight' ],
                    'section' => $value[ 'section' ]
                ];

                $this->container->callHook('block.section.update.before', [
                    $validator, &$data, $id
                ]);

                self::query()
                    ->update('block', $data)
                    ->where('block_id', '=', $idBlock)
                    ->execute();

                $this->container->callHook('block.section.update.after', [
                    $validator, $data, $id
                ]);
            }

            return $this->json(200, [
                    'messages' => [ 'success' => [ t('Saved configuration') ] ]
            ]);
        }

        return $this->json(400, [
                'messages'    => [ 'errors' => $validatorBlock->getKeyErrors() ],
                'errors_keys' => $validatorBlock->getKeyInputErrors()
        ]);
    }

    private static function getValidator(array $blocks = []): Validator
    {
        $validator = new Validator();

        foreach ($blocks as $key => $value) {
            $validator
                ->addRule("block[$key][section]", 'required|string|max:50')
                ->addRule("block[$key][weight]", 'required|numeric|between_numeric:0,50');
            $validator
                ->addInput("block[$key][section]", $value[ 'section' ] ?? null)
                ->addInput("block[$key][weight]", $value[ 'weight' ] ?? null)
            ;
        }

        return $validator;
    }
}
